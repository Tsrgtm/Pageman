<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Role Name
    |--------------------------------------------------------------------------
    |
    | This value defines the string that identifies an administrator role
    | within your application when using Pageman's role checking.
    | The HasPagemanUserRoles trait (specifically the isAdmin method)
    | will use this value.
    |
    */
    'admin_role_name' => env('PAGEMAN_ADMIN_ROLE', 'admin'),

    'auth' => [
        'route_prefix' => 'pageman/auth',
        'login_route' => 'pageman.auth.login', // Named route for login page
        'login_post_route' => 'pageman.auth.postLogin', // Named route for login form submission
        'logout_route' => 'pageman.auth.logout', // Named route for logout
        'dashboard_redirect_route' => 'pageman.admin.dashboard', // Where to redirect after successful login
    ],

    'admin' => [
        'route_prefix' => 'pageman/admin',
        'dashboard_route' => 'pageman.admin.dashboard', // Named route for admin dashboard
    ],

    /*
    |--------------------------------------------------------------------------
    | Other Pageman Configurations
    |--------------------------------------------------------------------------
    |
    | You can add other configuration options for your Pageman CMS here
    | as your package grows. For example:
    |
    | 'default_theme' => 'default',
    | 'media_disk' => 'public',
    |
    */
];