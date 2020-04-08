# Welcome to **Lull7**

Lull7 is the foundation of all my current REST APIs.  Its purposes and goals are as follows:

* Adding endpoints should be trivial. An happy corollary to this being that when things go wrong you should immediately have a good idea where to look.
* Provide some classes to manage common API features.  They can be extended as necessary, or ignored entirely.  Things like user auth, and even user spoofing.
* Integrated CLI support.  You can hit endpoints from the commandline.
* Easy support of multiple environments (Production, Development, Staging, etc.)

Lull7 allows me to quickly and easily roll up an API.

# Getting started

Clone the repo or download the files.  Place them where you'll be serving your API.  

#### Configuration

In the top-most directory,  you'll find a file called `config.default.php`.  I'll talk more about this later, but for now edit that file.  The settings most pertinent are under `config[system][database]`.

```php
'database' => [
	'host' => '',
	'username' => '',
	'password' => '',
	'database' => '',
]
```
Fill in your credentials there, however you like. If you'd like to pull them from the environment, feel free to do so.
You'll also want to change `client_url` to the public-facing location of your API.  As well as the `email[from]` email address.

Now that you're all configured, it's time to write some code for initialization.

### Initialization

Create a file called `setup.php` in the `endpoints/` directory, and paste in the following:

```php
<?php
namespace endpoints;
class SettingsEndpoint extends \altef\lull7\Endpoint {
	
	public function get($path, $data) {
		echo "Creating tables if they don't already exist...\n";
		$this->api->cache->_createTable();
		$this->api->users->_createTable();
		$this->api->settings->_createTable();
		
		echo "Adding a user if one doesn't exist...\n";
		if (!$this->api->users->exist()) {
			$password = $this->api->users->createHash();
			$username = 'email@address.com'; //  Replace this with your email address
			$user = $this->api->users->createUser( $username, $password);
			return [
				'username'=>$username,
				'password'=>$password,
				'id'=>$user
			];
		}
		return [];
	}
}
```

This is how endpoints are created in Lull7, and this one in particular creates some tables and adds a user account.  Feel free to modify this however you like, but be sure to swap in your own email address.

Open your web browser and proceed to your API's location, plus `/settings`.  You should see the output as defined above, and receive your initial users credentials.  Feel free to delete `settings.php` once you've done so.

Now your API foundation is all setup, and you've even created your first endpoint.  You may also want to delete `tests.php` and the `tests/` directory, but more on that later.

---

# How Lull7 works
Conceptually, lull7 is broken into two layers - the user-facing layer (endpoints) and the layer that does the work your endpoints request (modules accessible through the Api singleton).  However, if you only want to use the endpoint portion, that's fine too.

### How to make an endpoint, and similarly, how to find one

Endpoints are mapped directly to files.  Those files should be in the `endpoints/` directory, and extend `\altef\lull7\Endpoint`.  The file  `settings.php` from the previous section provides an example as to what they should look like.  Take a look at `\altef\lull7\Endpoint` for more details on what it provides.

There are functions for each of the common HTTP verbs: `get`, `put`, `post`, `delete`, and `options`.  Each of those receives two parameters: The path chunks that resulted in that endpoint's selection, and the data passed to it.  For PUT and DELETE the data is assumed to be JSON encoded in the request's payload.  For GET and POST, it comes from $_GET and $_POST respectively.  To use one in your endpoint, simply override it.

_Where_ you place the file determines how the endpoint will be accessed.  Lull7 will select the _most specific_ file from a number of possibilites.  Say you wanted to create the endpoint `/my/endpoint`.  You could do any of the following:
1. `endpoints/my/endpoint/index.php`
2. `endpoints/my/endpoint.php`
3. `endpoints/my/index.php` (and check the last portion of the path chunks to verify it is _endpoint_)
4. `endpoints/my.php` (and check the last portion of the path chunks to verify it is _endpoint_)
5. `endpoints/index.php` (and check the last two portions of the path chunks to verify they are _my_ and _endpoint_)
 
Since those are already organized into most to least specific, the first one Lull7 finds will be the file used. In this way it's simple to determine where to look for a particular endpoint's code.  It also makes it easy to break an endpoint up once should it become more complex.

So adding an endpoint is as simple as creating a maching PHP file.

#### Requiring parameters
It is commonplace for endpoints to require certain parameters.  To this effect, `\altef\lull7\Endpoint` supplies a function called `requireParam()`.  To use it, simply supply the parameters you require as a list, and a datasource to draw them from.  For example:

```php
...
public function get($path, $data) {
    $this->requireParam(['name', 'email'], $data);
    return [
        'name' => $data['name'],
        'email'=> $data['email']
    ];
}
...
```
You can now rest assured that 'name' and 'email' are keys in `$data`.  If either is not present, an API error will be outputted (`406`: `'name' is required.`, if `name` is missing).

#### Requiring login
Similarly, there is a `requireLogin()` function, which simply errors `401`: `Please login` if the current user is not logged in.  Feel free verify that the logged in user has access to the endpoint in question, using whatever permissions logic you prefer.  Personally, I store a JSON permissions object with each user. 

### An example endpoint

Create the file `endpoints/info.php`:
```php
<?php
namespace endpoints;

class InfoEndpoint extends \altef\lull7\Endpoint {
	
	public function get($path, $data) {
		header('Content-type: text/html');
		phpinfo();
		die();
	}
}
```
Navigating to `/info` should now show you the result of `phpinfo()`.  You may notice that I called `die()`.  Whatever your function returns will be output as JSON, unless you preempt that.

However there are some helpful functions to that regard in the API class, which I'll discuss more about later.

---

That is good introduction to the endpoint side of things, but now onto actually accomplishing tasks.  Take a look in `/lib/Api.class.php`.  As you can see, it extends `\altef\lull7\Api`.  You can alter this class however you like, or supply your own in its place.

Basically, it acts as a repository for modules (php classes) while also providing some helper functions for common tasks.

You should see in its constructor that we instantiate some classes and add them to it.

```php
$this->add('auth', new \altef\lull7\Auth($this->users, $this->session));
$this->add('settings', new \altef\lull7\Settings($this->system_db, 'settings'));
```

Anything you add in this way is accessible through the global Api singleton, which is also a member of all endpoints.  So if you want to access the `auth` instance from your endpoint, you can simply do `$this->api->auth`.

Write whatever modules are necessary and put them in in the `lib/` directory.  Then add them to the Api class, as above.  In your endpoint, make use of the functions they provide.

That's really all there is to it.  You can be as flexible as you need to be.

---

# The API class

As discussed above, the `API` singleton is a repository for your modules.  Your version extends `\altef\lull7\Api`, and I'll outline some the function that supplies below:

##### add($name, $object)
`add($name, $object)` is how you add an instance to the API.  This instance can then by accessed as a property of the API object.  So `$api->add('hello', 'cat');` would allow you to access the string 'cat' with `$api->hello`.

##### error($code, $description)
Aborts any further execution and sends out an API error.  For example `$api->error(400, "Bad request");`.  This function can be accessed statically.

##### finish($output)
Echos `$output` and aborts any further execution.

##### json_out($output)
Json encodes `$output`, echos it, and aborts any further execution.

##### isDebug()
Returns true if debug is enabled  for the current session.

##### setDebug(bool $v) 
Allows you to set the debug state for the current session.

---


# The endpoint class

Your endpoints should extend `\altef\lull7\Endpoint`.  Doing so gives them access to the API singleton via `$this->api`.  The functions it defines are below.

##### get($path_chunks, $data)
Processes an HTTP GET request.  `$data` contains the data passed in with the request.  `$path_chunks` contain the endpoint path requested split on `/`.  So `/my/endpoint/1` would be `['my', 'endpoint', '1']`.

##### post($path_chunks, $data)
Processes an HTTP POST request.

##### put($path_chunks, $data)
Processes an HTTP PUT request.

##### delete($path_chunks, $data)
Processes an HTTP DELETE request.

##### options($path_chunks, $data)
Processes an HTTP options request.

That covers the request handlers, but there are also some helper functions.

##### requireLogin()
Terminates in an API error if the user isn't logged in.

##### requireParam($list, $source)
Terminates in an API error if any element of `$list` is not a key in `$source`.

##### id($offset, $data)
Returns the element of `$data` at index `$offset`, or `false`.

##### id_int($offset, $data)
Is much the same, but returns the int value, or false.

---

# Configuration file(s)

The main configuration file is `config.default.php`.  It, and any domain-specific files, are simply PHP files which define or adjust the contents of a `$config` variable, which is a nested, associative array.  `config.default.php` is loaded first, followed by a domain-specific file, if appropriate, which allows you to overwrite values in the default config for that domain.  This is handled by `/lib/setup.inc.php:21`.

Personally, I only access the `$config` data in `index.php` and in the project's personal API extension class.  And occassionally the endpoints themselves.  That way the modules don't rely on it, but rather are configured by what you pass in when you instantiate them in the API class.

Feel free to add whatever you need to the config object.  I do.  For example, sometimes I separate the system data and the actual data into separate databases.

I'll talk a little about what's in the default config below.

##### Environments

`$config['environments']` allows you to map certain environments to a domain name.  Which in turn allows you to customize Lull7's config values on those domains.
If you define: `$config['environments']['local'] = 'localhost';`, Lull7 will attempt to load `config.default.php` and then `config.local.php`, should you access it from the domain `localhost`.

##### Headers
`$config['headers']` define the default headers the API should send out.  Feel free to change these, if you like.  You can send out headers on your own, as appropriate, in your endpoints as well.


##### System
The systems key holds a bunch of settings, but they all have to do with the Lull7 system.
* `messages` - Lull7 globally catches errors and exceptions when not in debug mode.  This section holds error messages to display should either of those occur.  Feel free to catch exceptions on your own, and output API errors in their stead.
* `database` - Is a spot to hold the system's database credentials.  Since the database's connection is handled in your own API class (see above), you can choose whether you want a database or not, or where to pull in the credentials from.
* `database.tables` - let's lull know the table names it should use for the `user`'s table, as well as the `persistent_global_map` table, so that you can rename them should you want to.  Currently the latter is used to cache user verification keys.
* `default_permissions` - an object that is JSON encoded and applied on the creation of new users, as per the `POST users` endpoint.  You can just handle it there instead, if you like.
 
##### Client_url
Used by the `/auth/forgot` endpoint to include a link in the email.

##### Email
Some basic email settings, including `from` and some `subject`s.

##### Verbosity
Defines the levels of verbosity when in and out of debug mode.

##### Email_template_directory
Where the email templates live.  Currently they're in `/email_templates/`.



---

# Session & Auth
You may have noticed in the API singleton that we have specified a type of `sessions`.  In the case of CLI, we use `\altef\keyvaluestore\LocalSoft`, and in every other case `\altef\keyvaluestore\Session`.  Don't feel tied to these, but I've found them to work well for my purposes.

When you log in by POSTing to the default `/auth` endpoint (with parameters `u` and `p`), if your credentials check out, your successful login is stored in your session.  One of the elements in the object you will get back is `sid`.  This is your session identifier, and it's how Lull7 remembers that you've logged in. What you do with that `sid` depends on your situation.  

1. Often PHP automatically passes it along in a cookie called `PHPSESSID`.  In which case your session is maintained for you.  This is true if you use `\altef\keyvaluestore\Session`, but not if you use one of the other keyaluestore classes (unless you tell it to).
2. However, you can manually specify an `sid` parameter along with your requests, and Lull7 will use that to restore your session.
3. Finally, you can supply the `Authorization` header, in the form `Authorization`: `Bearer [your-sid]`.  If that is supplied, Lull7 will use that value to restore your session.  I often go this route.

---

# Default endpoints
Lull7 provides some default endpoints.  Feel free to keep or remove them at your discretion.

#### `/debug`
The debug endpoint allows you to turn on, turn off, or check the status of `debug` for your session.  Errors will be hidden unless `debug` is `on`.

* `/debug` outputs your current debug status (`true` or `false`)
* `/debug/on`, `/debug/1`, `/debug/true` turns on debug
* `/debug/off`, `/debug/0`, `/debug/false` turns off debug.  Actually, anything that's not a true value turns off debug.
 
If you like, you can modify this endpoint to require user login, and respect `\altef\lyll7\Auth:canDebug()`.

#### `/settings`
Settings implements each of the HTTP request methods apart from OPTIONS.  It's provided mostly as an example, and handles user-level settings, so each method requires you be logged in.
* `GET /settings` returns all the settings for the current user
* `GET /settings/[name]` returns the value for a particular setting.  In this case `[name]`.
* `POST /settings/[name]` stores the setting `[name]`, and requires you pass in a `value` parameter.
* `PUT /settings/[name]` stores the setting `[name]`, and requires you pass in a `value` parameter.
* `DELETE /settings/[name]` deletes the setting `[name]`.

See the tests section for code samples on how they're used.

#### `/users`
Currently only allows you to create a user with `POST /users`.  Modify to your needs.

#### `/auth`
Handles logging in and out.

* `POST /auth` takes parameters `u` (email address) and `p` (password).  On successful login, it returns your user object along with an SID value to be used as your login token (see Session & Auth, above).
* `GET /auth` returns the user data of the user you're currently logged in as.
* `DELETE /auth` logs you out.

#### `/forgot`

* `GET /auth/forgot` Initiates the forgot-your-password flow.  It takes the param `u` (email address) and sends a password-reset for the user associated with that address via email.

#### `/reset`

* `GET /auth/reset` is the last portion of the forgot-your-password flow.  It takes `key` and `p` (password) as params, and resets the password of the user associated with `key` to `p`.  Keys expire after one day.

#### `/spoof`

* `GET /auth/spoof/[user_id]` swaps you to another user (`user_id`) if your user has permission to spoof users - as defined by the `\altef\lyll7\Auth:canSpoof()` which by default checks the user's permission object for: `['canSpoof'] == "1"`, but you can override it to work however you want.
This way you can check on what another user sees without having to get (or temporarily reset) their password. _Debugging_.

---

# Default modules

### `\altef\lull7\SimpleCRUD`
Provides simple `create`, `read`, `update`, and `delete` functions for a database table. 

```php
$crud = new \altef\lull7\SimpleCRUD($database, $tablename);
```

For the purposes of this example, we're going to assume a table named `people` which has a primary key `id` (which auto increments) and a `name`.

##### Creating
The `create($data)` function allows you to add rows, where `$data` defines the row you'd like to add.  Any properties of `$data` that aren't in the table will be ignored.  As well as any that map to auto incrementing columns.
It returns the new ID, if there's an auto increment column, or `true` one row has been added.
```php
$id = $crud->create(['name'=>'Zoe']); // Add jane to the table
$id2 = $crud->create(['id'=1, 'name'=>'Louis']); // The ID property will be ignored
```

##### Reading
The `read($where_map)` function selects from a table and returns an associative array with the results.  Calling `read()` with no parameter will return all rows in the table, but you can limit the rows requested by passing in an associative array of properties to match.

```php
$person1 = $crud->read(['id'=>1]);
$francis = $crud->read(['name'=>'Francis');
```

Any properties you pass in your `$where_map` that don't xist in the table will be ignored.

##### Updating
The `update($data)` function allows you to update a single row with `$data`.  It uses the keys found in `$data`, and returns `true` if one row was affected.

```php
$crud->update(['id'=>1, 'name'=>'Bill']); // Will update the person with id=1 to be named Bill
```

##### Deleting
The `del($data)` function allows you to delete a row by passing in an object with the keys that define it.  It returns `true` if one row was affected.

```php
$crud->del(['id'=>2]);
```

##### Soft Create
The `softCreate($data)` function allows you to `INSERT ON DUPLICATE KEY UPDATE`, where the update fields are all columns that aren't part of the primary key.  The ID of the affected row is returned, if there is an auto incrementing row.  Otherwise, `true` if one row is affected.
It is perhaps not best used with auto incrementing tables.


### `\altef\lull7\PDO`

Lull's (optional) PDO extension just cuts down a bit on the PDO code I have to write.  The constructor sets some default attributes, and some additional functions are provided.  All of these functions can be accessed statically or by instance.  Lull7's default modules don't assume this extension's use, which is why they're available statically. In the examples below, I will assume access through an instance. 

##### assoc($sql, $params=null, $mode=\PDO::FETCH_ASSOC)
Pass in a query with some params to replace `?`s, and receive the fetched result (by default an array of associative arrays).  It basically just handles the usual prepare/execute/fetch sequence in a single call.

```php
$data = $db->assoc('select * from people where name = ?', ['Zoey']);
```

#####  effect($sql, $params=null)
Similar to assoc, but rather than returning the result it returns the `statement`.  `Assoc()` makes use of it itself, and it's useful if you're not retrieving data but want some metadata about your statment.

##### success($statement)
Checks the error code of a statement.

```php
$s = $db->effect('delete from people where id=?', [1]);
if ($db->success($s))
    echo "Deleted!";
```


### `\altef\lull7\Users`

The Users module provides functions to deal with users.  

```php
$users = new \altef\lull7\Users($database);
```

##### _createTable()
Creates the table if it doesn't exist.  You probably saw reference to this in the **Getting started**'s `/setup` endpoint.

#### byId($id)
Returns a user with the `$id` passed.

#### exist()
Returns true if there is at least one user.

#### byUsername($username)
Returns a user with whose `email` matches `username`.

#### updateLastLogin($id)
Updates the last login date for the user with ID `$id`.

#### updateLastSeen($id)
Updates the last seen date for the user with ID `$id`.


#### forgotPassword($email)
Generates a password-reset key for the user associated with `$email`, and returns it.

#### resetPassword($key, $password)
Updates the user associated with the reset key `$key`'s password to `$password`.

#### byKey($key)
Returns the user associated with the reset key `$key`.

#### del($id)
Deletes the user with ID `$id`.

#### createUser($username, $password=null, $permissions=[])
Creates a user with the email address `$username`.  That user's password will be `$password`, unless `$password` is `null`.  In which case, a password is generated.
`$permissions` is the permissions object that will be associated with the user.

#### createHash($length=22, $sets=1)
Generates a string of psuedo-random characters.  The `$sets` param tells it which characters ranges to include.


### `\altef\lull7\Auth`
The Auth module module handles logging in and out.

#### userId()
Returns the ID of the currently logged in user, or throws an Exception.

#### login($username, $password)
Attempts to login with the passed credentials.  Returns `true` or `false`.

#### loginById($id)
Logs you in as the ser with ID `$id`.   Returns `true` or throws an Exception if no such user exists.

#### logout()
Logs you out.

#### data()
Returns the user data associated with the currently logged in user.

#### hasPermission($hierarchy)
This function is incomplete.  Take a look at it to see my intent, or ignore it entirely.

#### canSpoof()
Returns `true` if the user has permission to spoof other users.

#### canDebug()
Returns `true` if the user has permission to turn debugging on (for them).

---

# \altef\keyvaluestore\
Contains a bunch of (expiring) keyvaluestore classes backed in different ways, useful in different situations but for the most-part interchangeable since they all extend `\altef\keyvaluestore\KVSAbstract.php`.  Feel free to take a look at them for more info!

# \altef\output\
Contains the debug class and a basic email method.   Feel free to add your own email method, or to take a look at either file.

---

# Emails
The email class provided, in this case `\altef\output\email\Sendmail` is passed a template dir when constructed.  See `/email_templates/` for two samples.  You can check the `\auth\forgot` endpoint for a send example.

Emails provide a `send()` function which takes a number of arguments:

* `$to` The email address to send to.
* `$from` The email address the mail is from.
* `$subject` The subject of the email.
* `$template` The name of the template file (without ".html").
* `$data` An associative array of data.

The templating is very simple.  If you place `*|key|*` somewhere in the email template, and pass in `['key'=>'hello']` in as `$data`, `*|key|*` will be replaced by "hello" in the outgoing email.


# Tests
Sometimes you want to change code and then wonder if you've somehow, unknowingly, changed the interface by which you access that code.  Maybe it worries you.  It worries me.  So I wrote some tests.  You can write your own or delete them entirely, it's up to you!

These are how mine work:

Go to `tests.php`.  It'll show you a nested list of tests.  Click on a test to perform it.

The tests themselves are somewhere within the `/tests` directory.  Their location defines where they show up in the nested list.  `tests.php` automatically includes the appropriate test file when you run it.

To write a test, simple create a php file.  Now do whatever you want.  If you want to print some output, use the `p($string)` function.  All it does is wrap your text in `<p></p>` and output it.
If you want to check something, use the `affirm($bool)` function.  If you want to ouput some data `dump($data)`.  If you want to output some data as JSON, use `json($data)`.

Maybe you need to require user input.  You can do that!  User credentials, for example: 

`requiredInput($list, $description_if_not_supplied)`.

```php
if (requiredInput(['email', 'password'], "You must supply the email address and password of a valid user for this test.")) {
    // ... write test code here.  You now have access to $email and $password.
}
```

The tester will provide a fields for you to enter your input before running the test.

Maybe you need to perform a web request in your test (when testing an endpoint, for example).  `test.php` provides an easy way to do that.

`request($method, $url, $params=[], $headers=[])`

```php
	$data = request('POST', 'auth', ['u'=>$email, 'p'=>$password]);
	$data = json_decode($data, true);
	$headers = ['Authorization' => 'Bearer '.$data['sid']];
	$data = request('GET', 'auth', [], $headers);
```

Take a look at some of the tests if you're curious how to use the modules or endpoints provided, since they do just that.

# CLI

One thing I wanted lull7 to support was easily hitting endpoints from the command line.  To do so, use `index.php` accepts certain arguments.

* **user** the user ID to perform an action as.  `0` for anonymous/not-logged-in.
* **method** the HTTP request method (GET, POST, etc.).
* **environment** the environment to load the environment specific config for (`production`, for example).
* **path** the request UI for the endpoint to hit.

For example, to trigger a password reset from the command line, you could do something like:
```
php index.php --user=0 --method=GET --environment=production --path=auth/forgot?u=you@email.com
```




