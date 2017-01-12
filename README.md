Kispiox
=======

[![Build Status](https://travis-ci.org/kispiox/kispiox.svg?branch=master)](https://travis-ci.org/kispiox/kispiox)

Kispiox is a lightweight PHP web framework.

Installation
------------

Create a directory for your project, then install Kispiox via composer.

```
# mkdir myproject
# cd myproject
# composer require kispiox/kispiox
```

Next create an `index.php` with the following:

```php
<?php

require('vendor/autoload.php');

Kispiox/Kispiox::start();
```

Finally, update `compser.json` with your autoloading requirements.

```json
{
    "autoload": {
        "psr-4": {
            "\\My\\Project\\": "src/"
        }
    },
    "require": {
        "kispios/kispiox": "^1.0"
    }
}
```

And that's it! Make sure to add your classes in the `src/` directory.

Now t's time to create a *controller*.

Creating a Controller
---------------------

Controllers are the bridge between your application's business logic and the
framework's logic. Controllers field incoming requests, process those requests,
and then generate appropriate responses. The simplest way to create a controller
is to extend `Kispiox/Controller`, which provides a handful of useful methods.

Create a file called `MyController.php` in `src/` with the following:

```php
<?php

namespace My\Project;

use Kispiox/Controller;

class MyController extends Controller
{
}
```

Now that you've created the controller, it's time to add some *actions*. Actions
are the methods of a controller that are executed for particular requests.
Typically (but not always), actions will accept a request as a parameter and
return a response. An action would look something like:

```php
class MyController extends Controller
{
    public function someAction($request)
    {
        // do stuff
        return $response;
    }
}
```

A nice way to distinguish actions from other methods in your controller is to
suffix the method name with `Action`, however, this is not required (though it
is recommended for readability). In the action above, the `$request` parameter
will contain an instance of the incoming request. `$response` is an instance
of a generated response which will be sent back to the client.

To be nice and original, create an action that will display "Hello world!" in
plain text.

```php
class MyController extends Controller
{
    public function helloWorldAction()
    {
        return $this->textResponse('Hello world!');
    }
}
```

That's it! This action doesn't make use of the `$request` parameter because it
isn't needed. As well, it uses the helper method `$this->textResponse()` to
easily create a response containing the text `Hello world!`.

So how does a URI get mapped to an action anyway? Routes! That's how.

Mapping Requests to Actions Using Routes
----------------------------------------

*Routes* are rules that map a URI to an action. While routes can be defined in a
number of ways, the simplest is via YAML in `private/config/routes.yaml`. You'll
need to create `private/config` first.

```
# pwd
/home/user/myproject
# mkdir -p private/config
```

Now create a YAML file with the following:

```yaml
routes:
    - { path: '/hello', action: '\\My\\Project\\MyController:helloWorldAction' }
```

The above maps the path `/hello` to the fully-qualified class name of the
controller and the method name of the action (with a color separating the two).
When a request matching `/` is received, the HTTP dispatcher will call
`MyController::helloWorldAction()`. In turn, the action will generate a text
response it will return to the dispatcher, which will in turn output to the
client.

Routes are matched based on prefix, so any path beginnig with `/hello` will be
sent to the action.

### Additional Route Matching Criteria

Additional matching criteria can be specified. This includes the request method
along with header values. For, example:

```yaml
routes:
    - { method: 'GET', headers: { Host: 'example.com' }, path: '/hello', action: '\\My\\Project\\...' }
```

Now, in addition to matching the path `/hello`, a request must have been made
using the `GET` method and must contain a Host header set to `example.com`.

### Capturing Route Parameters

In many cases it can be useful to capture parts of the URL path for use by the
action. This can be accomplishe by enclosing a path segment (each segment being
the characters between slashes) in curly braces.

```yaml
routes:
    - { path: '/users/{user}', action: '\\My\\Project\\MyController:usersAction' }
```

The `user` parameter will be passed to the action as an argument, will contain
the value of the segment of the path. If the path was `/users/joe`, then `user`
will contain `joe`.

```php
    public function usersAction($user)
    {
        /*
         * If the matching path was /users/joe, then $user contains 'joe'
         */
    }
```
