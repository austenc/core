<?php namespace Hdmaster\Core;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Hdmaster\Core\Extensions\FormBuilder as Form;
use Illuminate\Html\HtmlBuilder;
use View;
use Illuminate\Support\Facades\Route;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Routes
        |--------------------------------------------------------------------------
        |
        | Loads all the default routes
        |
        */
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/routes.php';
        }
        
        // Helpers
        require __DIR__.'/helpers.php';

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        |
        | Loads filters
        |
        */
        require __DIR__.'/permissions.php';

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        |
        | Loads event listeners
        |
        */
        require __DIR__.'/events.php';

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        |
        | Loads filters
        |
        */
        require __DIR__.'/filters.php';

        /*
        |--------------------------------------------------------------------------
        | View Composers 
        |--------------------------------------------------------------------------
        |
        | Loads view composers which act as callbacks for when views are loaded.
        |
        */

        require __DIR__.'/composers.php';

        /*
        |--------------------------------------------------------------------------
        | Extensions
        |--------------------------------------------------------------------------
        |
        | Blade Extensions
        |
        */

        require __DIR__.'/extensions.php';

        /*
        |--------------------------------------------------------------------------
        | Macros
        |--------------------------------------------------------------------------
        |
        | Form and HTML extensions
        |
        */

        require __DIR__.'/macros.php';
        
        // Register the package migrations as a 'migrations' asset group
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'migrations');

        // Register our package's views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'core');

        // Load our package's translations
        $this->loadTranslationsFrom(__DIR__.'/Lang', 'core');

        // Specify public assets to be published
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/hdmaster/core')
        ], 'public');

        // Register middleware (since App::before filters are deprecated)
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Hdmaster\Core\Middleware\TempEmailAndDisciplineFilter');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAliases();
        $this->registerFormBuilder();

        // Register package commands
        $this->commands([
            'CoreDbCommand',
            'CoreSetupCommand',
            'CoreSeedCommand',
            'EventEndCommand',
            'ArchiveMaxAttempts',
            'CronCommand',
            'ExpireInstructors',
            'SetTestLock',
            'StudentArchiveTraining',
            'TestBuildCommand',
            'TestPublishCommand',
        ]);

        // Merge package config with one in outer app 
        // the app-level config will override the base package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/core.php', 'core'
        );

        // Bind our 'Flash' class
        $this->app->bindShared('flash', function () {
            return $this->app->make('Hdmaster\Core\Notifications\FlashNotifier');
        });

        // Register package dependencies
        $this->app->register('Collective\Html\HtmlServiceProvider');
        $this->app->register('Bootstrapper\BootstrapperL5ServiceProvider');
        $this->app->register('Codesleeve\LaravelStapler\Providers\L5ServiceProvider');
        $this->app->register('PragmaRX\ZipCode\Vendor\Laravel\ServiceProvider');
        $this->app->register('Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider');

        $this->app->register('Zizaco\Confide\ServiceProvider');
        $this->app->register('Zizaco\Entrust\EntrustServiceProvider');
    }

    /** 
     * Register our overridden form builder
     */
    private function registerFormBuilder()
    {
        $this->app->bind(
            'laravel5::html',
            function ($app) {
                return new HtmlBuilder($app->make('url'));
            }
        );
        $this->app->bind(
            'core::form',
            function ($app) {
                $form = new Form(
                    $app->make('laravel5::html'),
                    $app->make('url'),
                    $app['session.store']->getToken()
                );

                return $form->setSessionStore($app['session.store']);
            },
            true
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    /**
     * Register aliases
     *
     * @return void
     */
    protected function registerAliases()
    {
        $aliases = [
            'Actor'                   => 'Hdmaster\Core\Models\Actor\Actor',
            'Ada'                     => 'Hdmaster\Core\Models\Ada\Ada',
            'Admin'                   => 'Hdmaster\Core\Models\Admin\Admin',
            'Agency'                  => 'Hdmaster\Core\Models\Agency\Agency',
            'Billing'                 => 'Hdmaster\Core\Models\Billing\Billing',
            'BillingRate'             => 'Hdmaster\Core\Models\BillingRate\BillingRate',
            'Booking'                 => 'Hdmaster\Core\Models\Booking\Booking',
            'Certification'           => 'Hdmaster\Core\Models\Certification\Certification',
            'Discipline'              => 'Hdmaster\Core\Models\Discipline\Discipline',
            'Distractor'              => 'Hdmaster\Core\Models\Distractor\Distractor',
            'Enemy'                   => 'Hdmaster\Core\Models\Enemy\Enemy',
            'Exam'                    => 'Hdmaster\Core\Models\Exam\Exam',
            'Facility'                => 'Hdmaster\Core\Models\Facility\Facility',
            'InputField'              => 'Hdmaster\Core\Models\InputField\InputField',
            'Instructor'              => 'Hdmaster\Core\Models\Instructor\Instructor',
            'Observer'                => 'Hdmaster\Core\Models\Observer\Observer',
            'Payable'                 => 'Hdmaster\Core\Models\Payable\Payable',
            'PayableRate'             => 'Hdmaster\Core\Models\PayableRate\PayableRate',
            'Pendingevent'            => 'Hdmaster\Core\Models\Pendingevent\Pendingevent',
            'Pendingscore'            => 'Hdmaster\Core\Models\Pendingscore\Pendingscore',
            'Permission'              => 'Hdmaster\Core\Models\Permission\Permission',
            'PrintProfile'            => 'Hdmaster\Core\Models\PrintProfile\PrintProfile',
            'Proctor'                 => 'Hdmaster\Core\Models\Proctor\Proctor',
            'Role'                    => 'Hdmaster\Core\Models\Role\Role',
            'Skillattempt'            => 'Hdmaster\Core\Models\Skillattempt\Skillattempt',
            'Skillexam'               => 'Hdmaster\Core\Models\Skillexam\Skillexam',
            'Skilltask'               => 'Hdmaster\Core\Models\Skilltask\Skilltask',
            'SkilltaskResponse'       => 'Hdmaster\Core\Models\SkilltaskResponse\SkilltaskResponse',
            'SkilltaskSetup'          => 'Hdmaster\Core\Models\SkilltaskSetup\SkilltaskSetup',
            'SkilltaskStep'           => 'Hdmaster\Core\Models\SkilltaskStep\SkilltaskStep',
            'Skilltest'               => 'Hdmaster\Core\Models\Skilltest\Skilltest',
            'Staff'                   => 'Hdmaster\Core\Models\Staff\Staff',
            'Stat'                    => 'Hdmaster\Core\Models\Stat\Stat',
            'Student'                 => 'Hdmaster\Core\Models\Student\Student',
            'StudentExamEligibility'  => 'Hdmaster\Core\Models\StudentExamEligibility\StudentExamEligibility',
            'StudentSkillEligibility' => 'Hdmaster\Core\Models\StudentSkillEligibility\StudentSkillEligibility',
            'StudentTraining'         => 'Hdmaster\Core\Models\StudentTraining\StudentTraining',
            'Subject'                 => 'Hdmaster\Core\Models\Subject\Subject',
            'Testattempt'             => 'Hdmaster\Core\Models\Testattempt\Testattempt',
            'Testevent'               => 'Hdmaster\Core\Models\Testevent\Testevent',
            'Testform'                => 'Hdmaster\Core\Models\Testform\Testform',
            'Testitem'                => 'Hdmaster\Core\Models\Testitem\Testitem',
            'Testplan'                => 'Hdmaster\Core\Models\Testplan\Testplan',
            'Training'                => 'Hdmaster\Core\Models\Training\Training',
            'User'                    => 'Hdmaster\Core\Models\User\User',
            'UserRepository'          => 'Hdmaster\Core\Models\UserRepository\UserRepository',
            'Vocab'                   => 'Hdmaster\Core\Models\Vocab\Vocab',
            
            // Extra aliases
            'Flash'                   => 'Hdmaster\Core\Notifications\Flash',
            
            // Traits
            'Attemptable'             => 'Hdmaster\Core\Traits\Attemptable',
            'Attainable'              => 'Hdmaster\Core\Traits\Attainable',
            'Person'                  => 'Hdmaster\Core\Traits\Person',
            'Testteam'                => 'Hdmaster\Core\Traits\Testteam',
            'FacilityTrait'           => 'Hdmaster\Core\Traits\FacilityTrait',
            'StatusTrait'             => 'Hdmaster\Core\Traits\StatusTrait',
            'ClientOnlyTrait'         => 'Hdmaster\Core\Traits\ClientOnlyTrait',
            'LicenseTrait'            => 'Hdmaster\Core\Traits\LicenseTrait',
            
            // Scopes
            'ReschedulableScope'      => 'Hdmaster\Core\Scopes\ReschedulableScope',
            'ClientOnlyScope'         => 'Hdmaster\Core\Scopes\ClientOnlyScope',
            
            // Event Listeners
            'StudentListener'         => 'Hdmaster\Core\Listeners\StudentListener',
            
            // Helpers
            'BBCode'                  => 'Hdmaster\Core\Helpers\BBCode',
            'Formatter'               => 'Hdmaster\Core\Helpers\Formatter',
            'Importer'                => 'Hdmaster\Core\Helpers\Importer',
            'Readinglevel'            => 'Hdmaster\Core\Helpers\Readinglevel',
            'Sorter'                  => 'Hdmaster\Core\Helpers\Sorter',
            
            // PDFs
            'CertPdf'                 => 'Hdmaster\Core\Pdfs\CertPdf',
            'FormPdf'                 => 'Hdmaster\Core\Pdfs\FormPdf',
            'SkillPdf'                => 'Hdmaster\Core\Pdfs\SkillPdf',
            'ScanPdf'                 => 'Hdmaster\Core\Pdfs\ScanPdf',
            'Scanform'                => 'Hdmaster\Core\Pdfs\Scanform',
            'TaskPdf'                 => 'Hdmaster\Core\Pdfs\TaskPdf',
            'CoreSeeder'              => 'Hdmaster\Core\Seeds\DatabaseSeeder',
            
            // Commands
            'ArchiveMaxAttempts'      => 'Hdmaster\Core\Commands\ArchiveMaxAttempts',
            'CoreDbCommand'           => 'Hdmaster\Core\Commands\CoreDbCommand',
            'CoreSetupCommand'        => 'Hdmaster\Core\Commands\CoreSetupCommand',
            'CoreSeedCommand'         => 'Hdmaster\Core\Commands\CoreSeedCommand',
            'CronCommand'             => 'Hdmaster\Core\Commands\CronCommand',
            'EventEndCommand'         => 'Hdmaster\Core\Commands\EventEndCommand',
            'ExpireInstructors'       => 'Hdmaster\Core\Commands\ExpireInstructors',
            'SetTestLock'             => 'Hdmaster\Core\Commands\SetTestLock',
            'StudentArchiveTraining'  => 'Hdmaster\Core\Commands\StudentArchiveTraining',
            'TestBuildCommand'        => 'Hdmaster\Core\Commands\TestBuildCommand',
            'TestPublishCommand'      => 'Hdmaster\Core\Commands\TestPublishCommand',
            'Form'                    => 'Hdmaster\Core\Facades\Form',
            
            // Dependencies
            'Entrust'                 => \Zizaco\Entrust\EntrustFacade::class,
            // 'Confide'                 => \Zizaco\Confide\Facade::class,
            
            'Accordion'               => \Bootstrapper\Facades\Accordion::class,
            'Alert'                   => \Bootstrapper\Facades\Alert::class,
            'Badge'                   => \Bootstrapper\Facades\Badge::class,
            'Breadcrumb'              => \Bootstrapper\Facades\Breadcrumb::class,
            'Button'                  => \Bootstrapper\Facades\Button::class,
            'ButtonGroup'             => \Bootstrapper\Facades\ButtonGroup::class,
            'Carousel'                => \Bootstrapper\Facades\Carousel::class,
            'ControlGroup'            => \Bootstrapper\Facades\ControlGroup::class,
            'DropdownButton'          => \Bootstrapper\Facades\DropdownButton::class,
            'Helpers'                 => \Bootstrapper\Facades\Helpers::class,
            'Icon'                    => \Bootstrapper\Facades\Icon::class,
            'InputGroup'              => \Bootstrapper\Facades\InputGroup::class,
            'Image'                   => \Bootstrapper\Facades\Image::class,
            'Label'                   => \Bootstrapper\Facades\Label::class,
            'MediaObject'             => \Bootstrapper\Facades\MediaObject::class,
            'Modal'                   => \Bootstrapper\Facades\Modal::class,
            'Navbar'                  => \Bootstrapper\Facades\Navbar::class,
            'Navigation'              => \Bootstrapper\Facades\Navigation::class,
            'Panel'                   => \Bootstrapper\Facades\Panel::class,
            'ProgressBar'             => \Bootstrapper\Facades\ProgressBar::class,
            'Tabbable'                => \Bootstrapper\Facades\Tabbable::class,
            'Table'                   => \Bootstrapper\Facades\Table::class,
            'Thumbnail'               => \Bootstrapper\Facades\Thumbnail::class,
            
            'HTML'                    => \Collective\Html\HtmlFacade::class,
        ];

        $exclude = (array) $this->app['config']->get('core::config.excludeAliases');
        $this->loadAliases($aliases, $exclude);
    }

    protected function loadAliases($aliases, $exclude)
    {
        $loader = AliasLoader::getInstance();

        foreach ($aliases as $alias => $class) {
            if (! in_array($alias, $exclude)) {
                $loader->alias($alias, $class);
            }
        }
    }
}
