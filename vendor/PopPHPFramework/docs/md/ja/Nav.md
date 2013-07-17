Pop PHP Framework
=================

Documentation : Nav
-------------------

Home

ナビゲーションコンポーネントは、構成構築およびHTMLベースのナビゲーションツリーを出力する機能を提供します。また、必要に応じて、ナビゲーションツリー内の細粒度のアクセス制御のためのACLコンポーネントを利用することができる。

    use Pop\Auth;
    use Pop\Nav\Nav;

    // Set the roles and permissions for the Acl object
    $page = new Auth\Resource('page');
    $user = new Auth\Resource('user');

    $basic = Auth\Role::factory('basic')->addPermission('add');
    $editor = Auth\Role::factory('editor')->addPermission('add')
                                          ->addPermission('edit');

    $acl = new Auth\Acl();
    $acl->addRoles(array($basic, $editor));
    $acl->addResources(array($page, $user));

    $acl->allow('basic', 'page', array('add'))
        ->allow('editor', 'page')
        ->allow('editor', 'user');

    // Define the nav tree and it's config
    $tree = array(
        array(
            'name'     => 'Pages',
            'href'     => '/pages',
            'children' => array(
                array(
                    'name' => 'Add Page',
                    'href' => 'add',
                    'acl'  => array(
                        'resource'   => 'page',
                        'permission' => 'add'
                    )
                ),
                array(
                    'name' => 'Edit Page',
                    'href' => 'edit',
                    'acl'  => array(
                        'resource'   => 'page',
                        'permission' => 'edit'
                    )
                )
            )
        ),
        array(
            'name'     => 'Users',
            'href'     => '/users',
            'acl'  => array(
                'resource'   => 'user'
            ),
            'children' => array(
                array(
                    'name' => 'Add User',
                    'href' => 'add'
                ),
                array(
                    'name' => 'Edit User',
                    'href' => 'edit'
                )
            )
        )
    );

    $config = array(
        'top' => array(
            'id'    => 'main-nav'
        )
    );

    // Create the nav object and render it
    $nav = new Nav($tree, $config);
    $nav->setAcl($acl)
        ->setRole($editor);

    echo $nav;

\(c) 2009-2013 [Moc 10 Media, LLC.](http://www.moc10media.com) All
Rights Reserved.