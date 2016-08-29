<?php namespace Hdmaster\Core\Notifications;

use Carbon\Carbon;
use \User;
use View;

class Notification extends \Eloquent
{
    
    protected $fillable    = ['user_id', 'type', 'subject', 'body', 'object_id', 'object_type', 'sent_at'];
    private $relatedObject = null;
    
    /**
     * A notification has one user
     * @return Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get a m/d/Y string based on when notification was sent
     */
    public function getSentDateAttribute()
    {
        return date('m/d/Y', strtotime($this->sent_at));
    }

    /**
     * Date / time string the notification was sent like m/d/Y hr:min:s
     */
    public function getSentDatetimeAttribute()
    {
        return date('m/d/Y @ h:i:s T', strtotime($this->sent_at));
    }

    /**
     * Get body attribute without HTML tags
     */
    public function getFlatBodyAttribute()
    {
        return strip_tags($this->body);
    }

    /**
     * Get a '~ 3 days ago' style time value from the sent date
     */
    public function getTimeForHumansAttribute()
    {
        $date = new Carbon($this->sent_at);
        return $date->diffForHumans();
    }
 
    /**
     * Scope to get only unread messages easily
     * @param  $query
     * @return Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', '=', 0);
    }

    /**
     * Broadcast notification to multiple users
     *
     * @param   $users  array / collection of user models to broadcast to
     * @param   $options    specify the notification params:
     *     * 'subject' - the notification's subject
     *     * 'body'    - a string corresponding to a view name to load
     *     * 'params'  - parameter array that should be passed to the view
     *     * 'type'    - a message type -- 'info', 'warning', 'danger', 'success'
     *
     * Note that type and params for view are optional
     *
     * @return  boolean  returns true if notifications were sent, false if incorrect options passed in
     */
    public function broadcast($users, $options)
    {
        // Set some sensible message defaults
        $defaults = [
            'type'   => 'info',
            'params' => []
        ];

        $options = array_merge($defaults, $options);

        // No users, subject or body? Don't do anything
        if (empty($users) || empty($options['subject']) || empty($options['body'])) {
            return false;
        }

        // Make sure the body view params are an array
        if (! is_array($options['params'])) {
            $options['params'] = (array) $options['params'];
        }

        // Send a notification for each user
        foreach ($users as $u) {

            // generate body with/without view params
            $body = View::make($options['body'])->with($options['params']);

            $u->notify()->withType($options['type'])
                ->withSubject($options['subject'])
                ->withBody($body)
                ->deliver();
        }

        return true;
    }
 
    /**
     * Attaches / sets subject for a notification
     * @param  string   $subject
     * @return Notification
     */
    public function withSubject($subject)
    {
        $this->subject = $subject;
 
        return $this;
    }
 
     /**
     * Attaches / sets body for a notification
     * @param  mixed   $body
     * @return Notification
     */
    public function withBody($body)
    {
        // Is the body a string?
        if (is_string($body)) {
            $this->body = $body;
        }
        // Perhaps a view with a render method?
        elseif (method_exists($body, 'render')) {
            $this->body = $body->render();
        } else {
            throw new \Exception("Invalid notification body type - ".gettype($body), 1);
        }
 
        return $this;
    }
 
     /**
     * Attaches / sets type for a notification
     * @param  string   $type
     * @return Notification
     */
    public function withType($type)
    {
        $this->type = $type;
 
        return $this;
    }
 
     /**
     * Attaches a related model for a notification
     * @param  string   $subject
     * @return Notification
     */
    public function regarding($object)
    {
        if (is_object($object)) {
            $this->object_id   = $object->id;
            $this->object_type = $object->getMorphClass();
        }
 
        return $this;
    }
 
    /**
     * Sends this notification
     * @return Notification
     */
    public function deliver()
    {
        $this->sent_at = new Carbon;
        $this->save();
 
        return $this;
    }
 
    public function hasValidObject()
    {
        try {
            $object = call_user_func_array($this->object_type . '::findOrFail', [$this->object_id]);
        } catch (\Exception $e) {
            return false;
        }
 
        $this->relatedObject = $object;
 
        return true;
    }
 
    public function getObject()
    {
        if ($this->relatedObject) {
            $hasObject = $this->hasValidObject();
 
            if (!$hasObject) {
                throw new \Exception(sprintf("No valid object (%s with ID %s) associated with this notification.", $this->object_type, $this->object_id));
            }
        }
 
        return $this->relatedObject;
    }
}
