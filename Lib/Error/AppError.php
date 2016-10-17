<?php
    class AppError extends ErrorHandler
    {
            public function error404($params)
            {
                $this->controller->layout = 'error';
                parent::error404($params);
            }
    }
