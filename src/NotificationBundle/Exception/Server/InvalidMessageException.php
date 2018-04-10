<?php
/**
 * Created by PhpStorm.
 * User: versus
 * Date: 02/04/18
 * Time: 10:45
 */

namespace NotificationBundle\Exception\Server;


class InvalidMessageException extends \Exception
{
    protected $message = "Invalid message object";
}