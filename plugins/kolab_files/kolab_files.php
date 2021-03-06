<?php

/**
 * Kolab files storage
 *
 * @version @package_version@
 * @author Aleksander Machniak <machniak@kolabsys.com>
 *
 * Copyright (C) 2013, Kolab Systems AG <contact@kolabsys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class kolab_files extends rcube_plugin
{
    public $rc;
    public $home;
    private $engine;

    public function init()
    {
        $this->rc = rcmail::get_instance();

        // Register hooks
        $this->add_hook('keep_alive', array($this, 'keep_alive'));
        // Plugin actions
        $this->register_action('plugin.kolab_files', array($this, 'actions'));

        $ui_actions = array(
            'mail/compose',
            'mail/preview',
            'mail/show',
        );

        if (in_array($this->rc->task . '/' . $this->rc->action, $ui_actions)) {
            $this->ui();
        }
    }

    /**
     * Creates kolab_files_engine instance
     */
    private function engine()
    {
        if ($this->engine === null) {
            $this->load_config();

            $url = $this->rc->config->get('kolab_files_url');

            if (!$url) {
                return $this->engine = false;
            }

            require_once $this->home . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'kolab_files_engine.php';

            $this->engine = new kolab_files_engine($this, $url);
        }

        return $this->engine;
    }

    /**
     * Adds elements of files API user interface
     */
    private function ui()
    {
        if ($this->rc->output->type != 'html') {
            return;
        }

        if ($engine = $this->engine()) {
            $engine->ui();
        }
    }

    /**
     * Keep_alive hook handler
     */
    public function keep_alive($args)
    {
        // Here we are refreshing API session, so when we need it
        // the session will be active
        if ($engine = $this->engine()) {
            $this->rc->output->set_env('files_token', $engine->get_api_token());
        }

        return $args;
    }

    /**
     * Engine actions handler
     */
    public function actions()
    {
        if ($engine = $this->engine()) {
            $engine->actions();
        }
    }
}
