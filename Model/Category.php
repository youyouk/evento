<?php
class Category extends AppModel
{
    public $name = 'Category';
    public $useTable = 'categories';
    public $use = array('Event', 'Category');
    public $recursive = -1;

    /*
     * validation
     */

    public $validate = array(
        'name' => array(
            'length' => array(
                'rule' => array('minLength', 2),
                'message' => 'Name must be at least 2 characters long',
                'required' => true,
                'allowEmpty' => false,
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This category already exists',
            ),
    ), );

    /*
     * model associations
     */

    public $hasMany = array(
        'Event' => array('className' => 'Event'),
    );

    /**
     * create slug before saving.
     *
     * @param array $options
     *
     * @return bool
     */
    public function beforeSave($options = array())
    {
        $this->data['Category']['slug'] =
            Inflector::slug(strtolower(trim($this->data['Category']['name'])), '-');

        return parent::beforeSave($options);
    }

    /**
     * delete categories cache file after saving.
     *
     * @param bool  $created
     * @param array $options
     */
    public function afterSave($created, $options = array())
    {
        $this->__deleteCache();

        return parent::afterSave($created, $options);
    }

    /**
     * delete caches after deleting a category.
     */
    public function afterDelete()
    {
        $this->__deleteCache();
    }

    /**
     * bulk delete categories and all related events.
     *
     * @param array $ids
     */
    public function bulkDelete($ids)
    {
        $this->Event->recursive = -1;
        $events = $this->Event->find('all', array('conditions' => array('category_id' => $ids), 'fields' => array('id')));
        $this->Event->bulkDelete(Set::extract('/Event/id', $events));
        $this->deleteAll(array('Category.id' => $ids));
        $this->__deleteCache();

        return true;
    }

    /**
     * merge two different categories in one.
     *
     * @param int    $categoryId
     * @param string $categorySlug
     */
    public function merge($categoryId, $categorySlug)
    {
        $categoryMerge = $this->find('first', array('conditions' => array('Category.id' => $categoryId)));
        $categoryOriginal = $this->find('first', array('conditions' => array('Category.slug' => $categorySlug)));
        if (empty($categoryMerge) || empty($categoryOriginal)) {
            return false;
        }

        $this->Event->updateAll(array('Event.category_id' => $categoryOriginal['Category']['id']),
            array('Event.category_id' => $categoryMerge['Category']['id']));

        $this->delete($categoryMerge['Category']['id'], true);

        return true;
    }

    /**
     * delete categories cache.
     */
    private function __deleteCache()
    {
        Cache::delete('evento_categories');
        Cache::delete('evento_promoted');
        Cache::delete('evento_categorylist');

        return true;
    }
}
