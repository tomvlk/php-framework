<?php
/**
 * Welcome controller
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 * @date December 17th, 2015
 */

namespace App\Modules\Demo\Controllers;

use Nova\Core\View;
use App\Core\BaseModel;
use App\Modules\Demo\Core\BaseController;

use App\Modules\Demo\Models\Members as MembersModel;


/**
 * Sample Themed Controller with its typical usage.
 */
class Models extends BaseController
{
    private $model;


    /**
     * Call the parent construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = new MembersModel();
    }

    protected function beforeFlight()
    {
        // Leave to parent's method the Flight decisions.
        return parent::beforeFlight();
    }

    protected function afterFlight($result)
    {
        // Do some processing there, even deciding to stop the Flight, if case.

        // Leave to parent's method the Flight decisions.
        return parent::afterFlight($result);
    }

    /**
     * CakePHP style - Define Welcome page message and set the Controller's variables.
     */
    public function index()
    {
        $message = '';

        //
        $result = $this->model->countBy('username !=', 'admin');

        $message .= '<b>$this->model->countBy(\'username !=\', \'admin\');</b>';
        $message .= '<pre>'.var_export($result, true).'</pre><br>';

        //
        $members = $this->model->limit(2, 0)->findAll();

        $message .= '<b>$this->model->limit(2, 0)->findAll();</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        //
        $userInfo = array(
            'username' => 'virgil',
            'password' => 'test',
            'email'    => 'virgil@novaframwork.dev'
        );

        $message .= '<b>$userInfo</b>';
        $message .= '<pre>'.var_export($userInfo, true).'</pre><br>';

        //
        $retval = $this->model->insert($userInfo);

        $message .= '<b>$this->model->insert($userInfo);</b>';
        $message .= '<pre>'.var_export($retval, true).'</pre><br>';

        //
        $members = $this->model->findAll();

        $message .= '<b>$this->model->findAll();</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        //
        $userInfo = array(
            'password' => 'testing',
            'email'    => 'modified@novaframwork.dev'
        );

        $message .= '<b>$userInfo</b>';
        $message .= '<pre>'.var_export($userInfo, true).'</pre><br>';

        //
        $retval = $this->model->updateBy('username', 'virgil', $userInfo);

        $message .= '<b>$this->model->updateBy(\'username\', \'virgil\', $userInfo);</b>';
        $message .= '<pre>'.var_export($retval, true).'</pre><br>';

        //
        $members = $this->model->findAll();

        $message .= '<b>$this->model->findAll();</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        //
        $retval = $this->model->deleteBy('username', 'virgil');

        $message .= '<b>$this->model->deleteBy(\'username\', \'virgil\');</b>';
        $message .= '<pre>'.var_export($retval, true).'</pre><br>';

        //
        $members = $this->model->order('desc')->findAll();

        $message .= '<b>$this->model->order(\'desc\')->findAll();</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        //
        $members = $this->model->orderBy('username', 'desc')->limit(2, 0)->findAll();

        $message .= '<b>$this->model->orderBy(\'username\', \'desc\')->limit(2, 0)->findAll();</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        //
        $result = $this->model->findBy('username', 'marcus');

        $message .= '<b>$this->model->findBy(\'username\', \'marcus\');</b>';
        $message .= '<pre>'.var_export($result, true).'</pre><br>';

        //
        $result = $this->model->find(3);

        $message .= '<b>$this->model->find(3);</b><pre>'.var_export($result, true).'</pre><br>';

        //
        $members = $this->model->orderBy('username', 'desc')->findMany(array(1, 3));

        $message .= '<b>$this->model->orderBy(\'username\', \'desc\')->findMany(array(1, 3));</b>';
        $message .= '<pre>'.var_export($members, true).'</pre><br>';

        // Setup the View variables.
        $this->title(__d('demo', 'Base Model Demo'));

        $this->set('message', $message);
    }

}
