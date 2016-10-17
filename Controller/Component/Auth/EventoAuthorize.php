<?php

App::uses('BaseAuthorize', 'Controller/Component/Auth');
App::uses('Group', 'Model');

class EventoAuthorize extends BaseAuthorize
{
    /**
     * Check permissions to access the admin and the Events ACOs.
     *
     * @param array       $user
     * @param CakeRequest $request
     */
    public function authorize($user, CakeRequest $request)
    {
        $Acl = $this->_Collection->load('Acl');
        $group = new Group();
        $group->id = $user['group_id'];

        if (!empty($request->params['admin']) && isset($request->params['admin'])) {
            return $Acl->check($group, 'admin');
        }

        if ($this->_Controller->name == 'Events') {
            return $Acl->check($group, 'Events');
        }

        return true;
    }
}
