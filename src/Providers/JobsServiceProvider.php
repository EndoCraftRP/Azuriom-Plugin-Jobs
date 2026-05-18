<?php

namespace Azuriom\Plugin\Jobs\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;

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
                    'jobs.admin.applications.index' => trans('jobs::messages.admin_applications'),
                    'jobs.admin.positions.index' => trans('jobs::messages.admin_positions'),
                    'jobs.admin.settings.edit' => trans('jobs::messages.admin_settings'),
                ],
            ],
        ];
    }

    protected function registerPermissions(): void
    {
        Permission::registerPermissions([
            'jobs.manage' => 'jobs::messages.permission_manage',
        ]);
    }
}
