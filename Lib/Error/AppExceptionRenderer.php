<?php

App::uses('ExceptionRenderer', 'Error');

class AppExceptionRenderer extends ExceptionRenderer
{
    public function missingController($error)
    {
        $this->controller->response->statusCode(404);
        $this->controller->render('/Errors/error404', 'notFound');
        $this->controller->response->send();
    }

    public function missingAction($error)
    {
        $this->missingController($error);
    }

    public function notFound($error)
    {
        $this->missingController($error);
    }
}
