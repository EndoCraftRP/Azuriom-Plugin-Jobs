<?php

namespace Azuriom\Plugin\Jobs\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Jobs\Models\Application;

class JobsServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();
        $this->registerPermissions();
        $this->registerLogs();
    }

    protected function registerLogs(): void
    {
        ActionLog::registerLogs([
            'jobs.applications.status' => [
                'icon' => 'arrow-repeat',
                'color' => 'info',
                'message' => 'jobs::messages.logs.applications.status',
                'model' => Application::class,
            ],
            'jobs.applications.updated' => [
                'icon' => 'pencil-square',
                'color' => 'primary',
                'message' => 'jobs::messages.logs.applications.updated',
                'model' => Application::class,
            ],
        ]);
    }

    protected function routeDescriptions(): array
    {
        return [
            'jobs.index' => trans('jobs::messages.nav_title'),
        ];
    }

    protected function adminNavigation(): array
    {
        return [
            'jobs' => [
                'name' => trans('jobs::messages.admin_nav'),
                'type' => 'dropdown',
                'icon' => 'bi bi-person-badge',
                'route' => 'jobs.admin.*',
                'items' => [
                    'jobs.admin.applications.index' => [
                        'name' => trans('jobs::messages.admin_applications'),
                        'permission' => 'jobs.applications',
                    ],
                    'jobs.admin.positions.index' => [
                        'name' => trans('jobs::messages.admin_positions'),
                        'permission' => 'jobs.admin',
                    ],
                    'jobs.admin.settings.edit' => [
                        'name' => trans('jobs::messages.admin_settings'),
                        'permission' => 'jobs.admin',
                    ],
                ],
            ],
        ];
    }

    protected function registerPermissions(): void
    {
        Permission::registerPermissions([
            'jobs.admin' => 'jobs::messages.permission_admin',
            'jobs.applications' => 'jobs::messages.permission_applications',
        ]);
    }
}
