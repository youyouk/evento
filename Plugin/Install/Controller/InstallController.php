<?php
class InstallController extends InstallAppController
{
    public $name = 'Install';
    public $uses = null;
    public $components = array('Session');

    /*
     * default settings for the plugin
     */

    public $settings = array(
        'database_defaults' => array(
            'host'        => 'localhost',
            'database'    => '',
            'login'    => '',
            'password'    => '',
        ),
        'is_writable' => array(),
    );

    /**
     * if database.php already exists throw 404 error.
     */
    public function beforeFilter()
    {
        $this->Auth->allow();
        if (file_exists(APP.'Config'.DS.'database.php')) {
            throw new NotFoundException();
        }

        $this->settings['is_writable'] = array(
            APP.'Config',
            TMP,
            TMP.'cache/',
            TMP.'cache/models/',
            TMP.'cache/persistent/',
            TMP.'cache/views/',
            TMP.'logs/',
            TMP.'sessions/',
            TMP.'tests/',
        );

        if (file_exists(APP.'Config'.DS.'install_settings.php')) {
            require_once APP.'Config'.DS.'install_settings.php';
        }

        if (isset($installSettings)) {
            $this->settings = Set::merge($this->settings, $installSettings);
        }

        return parent::beforeFilter();
    }

    /**
     * installation method checks directories permissions and
     * creates the database.php file with the user database name, host, username and password.
     */
    public function index()
    {
        $this->set('is_writable', $this->settings['is_writable']);

        if (!empty($this->request->data)) {
            // test database connection

            if (@mysql_connect($this->data['Install']['host'],
                $this->request->data['Install']['login'], $this->data['Install']['password'])
                && mysql_select_db($this->request->data['Install']['database'])) {
                copy(APP.'Config'.DS.'database.php.install', APP.'Config'.DS.'database.php');

                    // open database.php file
                    App::import('Utility', 'File');
                $file = new File(APP.'Config'.DS.'database.php', true);
                $content = $file->read();

                    // write database.php file
                    $content = str_replace('{default_host}', $this->request->data['Install']['host'], $content);
                $content = str_replace('{default_login}', $this->request->data['Install']['login'], $content);
                $content = str_replace('{default_password}', $this->request->data['Install']['password'],
                        $content);
                $content = str_replace('{default_database}', $this->request->data['Install']['database'],
                        $content);

                if ($file->write($content)) {
                    App::import('Model', 'ConnectionManager');
                    $db = ConnectionManager::getDataSource('default');
                    $this->__executeSQLScript($db, APP.'Config'.DS.'Schema'.DS.'install.sql');
                    $this->Session->setFlash(__('Admin registration form.'));
                    $this->redirect(array('plugin' => null, 'controller' => 'users', 'action' => 'register'));
                } else {
                    $this->Session->setFlash(__('Could not write app/Config/database.php file. Check permissions!'));
                }
            } else {
                $this->Session->setFlash(__('Could not connect to database. Please make sure the database exists
				and the user has permissions to write the tables.', true));
            }
        } else {
            $this->request->data['Install'] = $this->settings['database_defaults'];
        }
    }

    /**
     * Execute SQL file.
     *
     * @link http://cakebaker.42dh.com/2007/04/16/writing-an-installer-for-your-cakephp-application/
     *
     * @param object $db       Database
     * @param string $fileName sql file
     */
    public function __executeSQLScript($db, $fileName)
    {
        $statements = file_get_contents($fileName);
        $statements = explode(';', $statements);

        foreach ($statements as $statement) {
            if (trim($statement) != '') {
                $db->query($statement);
            }
        }
    }
}
