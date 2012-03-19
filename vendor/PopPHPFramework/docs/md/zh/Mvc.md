Pop PHP Framework
=================

Documentation : Mvc
-------------------

MVC组件，文档中概述的概述，是MVC设计模式的实现，用的路由器，以方便多个用户的路径和控制器的附加层。简而言之，控制器处理请求的代表团，该模型处理业务逻辑和视图决定如何显示给用户的输出。此组件内的所有这些类是很容易扩展，利用他们在自己的应用程序。


虽然这可能看起来过于复杂，如果你使用CLI组件项目安装功能，大部分代码可以书面和为您安装。你只需要定义的项目名称，并在安装配置文件的设置。查看项目组件DOC文件得到一个项目的安装配置文件的一个例子。


<pre>
use Pop\Mvc\Controller,
    Pop\Mvc\Model,
    Pop\Mvc\Router,
    Pop\Mvc\View,
    Pop\Project\Project;

// Define your project class
class MyProject extends Project
{
    // Extend the parent 'run' method to establish router paths
    public function run()
    {
        parent::run();

        if ($this->router()->controller()->getRequest()->getRequestUri() == '/') {
            $this->router()->controller()->dispatch();
        } else if (method_exists($this->router()->controller(), $this->router()->getAction())) {
            $this->router()->controller()->dispatch($this->router()->getAction());
        } else if (method_exists($this->router()->controller(), 'error')) {
            $this->router()->controller()->dispatch('error');
        }
    }
}

class MyModel extends Model
{
    // Perhaps does something special pertaining to whatever data you are manipulating
}

class MyController extends Controller
{
    // Constructor
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        if (null === $viewPath) {
            $viewPath = __DIR__ . '/path/to/my/view/default';
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    // Your home page
    public function index()
    {
        $model = new MyModel(array('username' => 'myusername');
        $this->view = View::factory($this->viewPath . '/index.phtml', $model);
        $this->send();
    }

    // Your 404 page
    public function error()
    {
        $this->isError = true;
        $this->view = View::factory($this->viewPath . '/error.phtml');
        $this->send();
    }
}

// Create a project object, to define the project config, router and controller(s)
$project = MyProject::factory(
    include '../some/config/project.config.php',
    include '../some/config/module.config.php',
    new Router(array(
        'default' => 'MyApp\\MyController'
    ))
);

// Run the project
$project->run();
</pre>

(c) 2009-2012 [Moc 10 Media, LLC.](http://www.moc10media.com) All Rights Reserved.