<?php
class CategoriesController extends AppController
{
    public $name = 'Categories';

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////////

    /**
     * set categories variable to display the categpories table to admin.
     */
    public function admin_index()
    {
        $this->set('categories', $this->Category->find('all', array(
            'order' => 'Category.name',
            'fields' => array('Category.name', 'Category.id'), )
        ));
        $this->set('title_for_layout', __('Manage categories'));
    }

    /**
     * add new category.
     */
    public function admin_add()
    {
        if (!empty($this->request->data) && $this->Category->save($this->request->data)) {
            $this->redirect(array('action' => 'index'));
        }
        $this->set('title_for_layout', __('Add a new category'));
    }

    /**
     * edit category.
     *
     * @param int $categoryId
     */
    public function admin_edit($categoryId = null)
    {
        if (!$categoryId) {
            throw new NotFoundException();
        }
        if (!empty($this->request->data)) {
            if ($this->Category->save($this->request->data)) {
                $this->redirect(array('action' => 'index'));
            }
        } else {
            $category = $this->Category->find('first', array('conditions' => array('id' => $categoryId),
                'fields' => array('Category.id', 'Category.name'), ));
            if (empty($category)) {
                throw new NotFoundException();
            }
            $this->request->data = $category;
        }
        $this->set('title_for_layout', __('Edit category'));
    }

    /**
     * delete category.
     *
     * @param int  $categoryId
     * @param bool $confirmation
     */
    public function admin_delete($categoryId = null, $confirmation = false)
    {
        if (!$categoryId) {
            throw new NotFoundException();
        }
        $this->Category->Event->recursive = -1;
        $events = $this->Category->Event->find('all', array(
            'conditions' => array('Event.category_id' => $categoryId),
            'fields' => array('Event.id'),
        ));

        if ((!empty($events) && $confirmation != false) || empty($events)) {
            $this->Category->Event->bulkDelete(Set::extract('/Event/id', $events));
            $this->Category->delete($categoryId);
            $this->redirect(array('action' => 'index'));
        }
        $this->set('id', $categoryId);
        $this->set('events', count($events));
    }

    /**
     * merge two different categories in one.
     *
     * @param int    $categoryId
     * @param string $categorySlug
     */
    public function admin_merge($categoryId = null, $categorySlug = null)
    {
        if ($categoryId === null || $categorySlug === null) {
            throw new NotFoundException();
        }
        $this->Category->merge($categoryId, $categorySlug);
        $this->redirect(array('action' => 'index'));
    }

    /**
     * allow admin to search tags.
     */
    public function admin_search()
    {
        $this->set('title_for_layout', __('Search'));
        $data = $this->Session->read('Search.term');
        if (!empty($this->request->data) || !empty($data)) {
            if ($this->request->data) {
                $this->Session->write('Search.term', $this->request->data);
            } else {
                $this->request->data = $data;
            }
            $conditions = array('Category.name like' => '%'.$this->request->data['Search']['category'].'%');
            $this->set('categories', $this->paginate('Category', $conditions));
        }
        $this->render('admin_index');
    }

    /**
     * bulk manage categories.
     */
    public function admin_bulk()
    {
        if (empty($this->request->data)) {
            throw new NotFoundException();
        }
        $ids = array();
        foreach ($this->request->data['Category']['id'] as $key => $value) {
            if ($value) {
                $ids[] = $key;
            }
        }
        $this->Category->bulkDelete($ids);
        $this->redirect(array('action' => 'index'));
    }
}
