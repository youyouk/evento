<?php
class TagsController extends AppController
{
    public $name = 'Tags';
    public $uses = array('Tag');
    public $paginate = array('limit' => 30, 'order' => array('Tag.name' => 'asc'));

    /**
     * Function for the Ajax autocompleter used for the tag names.
     * It gets the last tag the user writes and looks for it in database.
     * Used in the add form for the Events model.
     *
     * @access public
     */
    public function autoComplete()
    {
        $this->layout = 'ajax';
        if (!strrpos($this->request->data['Event']['tags'], ',')) {
            $tag = $this->request->data['Event']['tags'];
        } else {
            $tag = substr($this->request->data['Event']['tags'],
                strrpos($this->request->data['Event']['tags'], ',') + 1);
            $tag = trim($tag);
            if (strlen($tag) < 1) {
                $this->set('tags', array());

                return false;
            }
        }

        $conditions = array('Tag.name LIKE' => $tag.'%');
        $this->set('tags', $this->Tag->find('all', array(
            'conditions' => $conditions, 'fields' => array('name'), )));
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////////

    /**
     * set tags variable to display the tags table to admin.
     */
    public function admin_index()
    {
        $this->paginate['fields'] = array('Tag.id', 'Tag.name');
        $this->Session->delete('Search.term');
        $this->set('tags', $this->paginate('Tag'));
        $this->set('title_for_layout', __('Manage tags'));
    }

    /**
     * edit a tag name.
     *
     * @param int $id
     */
    public function admin_edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException();
        }
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $action = 'index';
        if ($this->Session->read('Search.term')) {
            $action = 'search';
        }

        if (!empty($this->request->data)) {
            if ($this->Tag->save($this->request->data)) {
                $this->redirect(array('action' => $action, 'page' => $page));
            }
        } else {
            $tag = $this->Tag->find('first', array('conditions' => array('Tag.id' => $id),
                'fields' => array('Tag.id', 'Tag.name'), ));
            if (empty($tag)) {
                throw new NotFoundException();
            }
            $this->request->data = $tag;
        }
        $this->set('page', $page);
        $this->set('title_for_layout', __('Edit tag'));
    }

    /**
     * delete tag.
     *
     * @param int $id
     */
    public function admin_delete($id = null)
    {
        if (!$id) {
            throw new NotFoundException();
        }
        $this->Tag->delete($id);

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $conditions = null;
        $action = 'index';
        if ($this->Session->read('Search.term')) {
            $action = 'search';
            $term = $this->Session->read('Search.term');
            $conditions = array('Tag.name like' => '%'.$term['Search']['tag'].'%');
        }
        if ($page) {
            $count = $this->Tag->find('count', array('conditions' => $conditions));
            $lastPage = ceil($count / $this->paginate['limit']);
            if ($page > $lastPage) {
                $page = $lastPage;
            }
        }
        $this->redirect(array('action' => $action, 'page' => $page));
    }

    /**
     * merge two different tags in one.
     *
     * @param int    $tagId
     * @param string $tagSlug
     */
    public function admin_merge($tagId = null, $tagSlug = null)
    {
        if (!$tagId || $tagSlug === null) {
            throw new NotFoundException();
        }
        $this->Tag->merge($tagId, $tagSlug);
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
            $conditions = array('Tag.name like' => '%'.$this->request->data['Search']['tag'].'%');
            $this->set('tags', $this->paginate('Tag', $conditions));
        }
        $this->render('admin_index');
    }

    /**
     * bulk manage tags.
     */
    public function admin_bulk()
    {
        if (empty($this->request->data)) {
            throw new NotFoundException();
        }
        $ids = array();
        foreach ($this->request->data['Tag']['id'] as $key => $value) {
            if ($value != 0) {
                $ids[] = $key;
            }
        }
        switch ($this->request->data['Tag']['option']) {
            case 'delete':
                $this->Tag->bulkDelete($ids);
                break;
        }
        $this->redirect(array('action' => 'index'));
    }
}
