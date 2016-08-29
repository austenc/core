<?php

View::composer('core::partials.flash_messages', 'Hdmaster\Core\Composers\FlashComposer');
View::composer('core::navigation.menu', 'Hdmaster\Core\Composers\NavigationComposer');
View::composer('core::navigation.partials.top', 'Hdmaster\Core\Composers\NavigationComposer');
View::composer('core::events.sidebars.index', 'Hdmaster\Core\Composers\EventSidebarComposer');
View::composer('core::layouts.default', 'Hdmaster\Core\Composers\LayoutComposer');
View::composer('core::layouts.full', 'Hdmaster\Core\Composers\LayoutComposer');
View::composer('core::events.calendar_legend', 'Hdmaster\Core\Composers\CalendarLegendComposer');
