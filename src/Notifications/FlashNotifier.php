<?php namespace Hdmaster\Core\Notifications;

use Illuminate\Session\Store;

class FlashNotifier
{

    private $session;

    public function __construct(Store $session)
    {
        $this->session = $session;
    }

    /**
     * Pushes a flash message onto the respective array
     * @param  $message
     * @param  mixed
     */
    public function message($message, $title = null, $type='_info')
    {
        // multiple messages to push onto the array?
        $messages = array_merge((array) $message, (array) $this->session->get($type));

        // put merged arrays back in session
        $this->session->put($type, $messages);

        // Set title for this type if it isn't null
        $title == null ?: $this->session->put('flash_title.'.$type, $title);
    }

    /**
     * Send an info flash
     */
    public function info($message, $title = null)
    {
        $this->message($message, $title);
    }

    /**
     * Send a warning
     */
    public function warning($message, $title = null)
    {
        $this->message($message, $title, '_warning');
    }

    /**
     * Danger!
     */
    public function danger($message, $title = null)
    {
        $this->message($message, $title, '_danger');
    }

    /**
     * Success flash
     */
    public function success($message, $title = null)
    {
        $this->message($message, $title, '_success');
    }
}
