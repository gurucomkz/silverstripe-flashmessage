<?php
namespace axyr\flashmessage;

use BadMethodCallException;
use SilverStripe\View\ViewableData;
use SilverStripe\View\TemplateGlobalProvider;

use SilverStripe\View\Requirements;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;

/**
 * Class Flash
 * @method static void info(string $message, bool $closable = null, bool $fadeOut = null)
 * @method static void success(string $message, bool $closable = null, bool $fadeOut = null)
 * @method static void warning(string $message, bool $closable = null, bool $fadeOut = null)
 * @method static void danger(string $message, bool $closable = null, bool $fadeOut = null)
 * @method static void alert(string $message, bool $closable = null, bool $fadeOut = null)
 * @method static void modal(string $message, bool $closable = null, bool $fadeOut = null)
 */
class Flash extends ViewableData implements TemplateGlobalProvider
{
    /**
     * @config
     * @var array
     */
    private static $defaults = [
        'Type'     => 'success',
        'IsModal'  => false,
        'Closable' => true,
        'FadeOut'  => false
    ];

    /**
     * @config
     * @var array
     */
    private static $supported_methods = [
        'info',
        'success',
        'warning',
        'danger',
        'alert',
        'modal'
    ];

    /**
     * @config
     * @var string
     */
    private static $template = 'FlashMessage';

    /**
     * @config
     * @var string
     */
    private static $session_name = 'FlashMessage';

    /**
     * @config
     * @var bool
     */
    private static $load_javascript = true;

    /**
     * The Flash Message data
     *
     * @var array
     */
    protected $data = [];
 /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = (array)$data + (array)self::config()->get('defaults');

        parent::__construct();
    }

    /**
     * @return array
     */
    public static function get_template_global_variables()
    {
        return [
            'FlashMessage'
        ];
    }

    /**
     * @return Flash
     */
    public static function FlashMessage()
    {
        return Flash::get();
    }

    /**
     * @param $message
     * @param string $type
     * @param null $closable
     * @param null $fadeOut
     */
    public static function set($message, $type = 'success', $closable = null, $fadeOut = null)
    {

        $data = [
            'Message' => $message,
            'Type'    => $type
        ];

        if (null !== $closable) {
            $data['Closable'] = $closable;
        }

        if (null !== $fadeOut) {
            $data['FadeOut'] = $fadeOut;
        }

        if ('modal' === $type) {
            $data['IsModal'] = true;
        }

        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();

        $session->set(Flash::config()->get('session_name'), $data);
    }

    /**
     * @return Flash
   */
    public static function get()
    {

        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();

        $key  = Flash::config()->get('session_name');
        $data = $session->get($key);

        $session->clear($key);

        return (new Flash($data));
    }

    /**
     * @return string
     */
    public function forTemplate()
    {
        if (self::config()->get('load_javascript')) {
             Requirements::javascript('axyr/silverstripe-flashmessage: client/javascript/flashmessage.js');
        }
        return $this->renderWith(self::config()->get('template'), $this->data);
    }

    /**
     * @param $method
     * @param $args
     * @throws BadMethodCallException
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::config()->get('supported_methods'))) {
            self::set($args[0], $method, isset($args[1]) ? $args[1] : null, isset($args[2]) ? $args[2] : null);
        } else {
            throw new BadMethodCallException("Method '$method' does not exist on " . __CLASS__);
        }
    }
}
