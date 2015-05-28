<?php namespace Mitul\Controller;

use App\Http\Controllers\Controller as Controller;
use Mitul\Generator\Errors;

class AppBaseController extends Controller
{
    /**
     * Allowed HTTP requests
     * @var string
     */
    protected static $hateoas = [];

    /**
     * Validate request for current resource
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function validateRequestOrFail($request, array $rules, $messages = [], $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            Errors::throwHttpExceptionWithCode(Errors::VALIDATION_ERROR, $validator->errors()->getMessages());
        }
    }

    /**
     * Generates result response object
     *
     * @param mixed $data Response data
     * @param string $message Response description
     * @param bool $with_links HATEOAS Send links
     * @param array $links_replace [placeholder > value] Replacements inside hrefs
     * @return array
     */
    protected function structurizeResponse($data = null, $message = '', $with_links = true, array $links_replace = [])
    {
        $result = array();
        if ($message) {
            $result['message'] = $message;
        }
        if ($data !== null) {
            $result['data'] = $data;
        }
        if ($with_links) {
            $result['links'] = static::getHATEOAS($links_replace);
        }

        return $result;
    }

    /**
     * Make API navigation for current controller from static::$hateoas
     *
     * @param array $links_replace [placeholder > value] Replacements inside hrefs in static::$hateoas
     * @return mixed
     */
    public static function getHATEOAS(array $links_replace = [])
    {
        $links = static::$hateoas;
        if (!empty($links_replace)) {
            foreach ($links as $key => $link) {
                $links[$key]['href']
                    = str_replace(array_keys($links_replace), array_values($links_replace), $link['href']);
            }
        }

        $links['self'] = [
            'href' => [\Request::server('REQUEST_URI')],
            'methods' => [\Request::server('REQUEST_METHOD')],
        ];

        return $links;
    }
}