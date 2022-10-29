# Description
The script will start a quick mock server for REST API testing. 

# Usage
1. Please refer to Examples section.
2. Change `./dev/mocks.json` according to your needs. Supported placeholders are {{VARIABLE}}
3. Add an output file to './apis/XXXX' (supports .json and .xml otherwise will output as text/plain). Supported 
   placeholders are put as {{VARIABLE}}
4. Start a php server
```shell
php -S 127.0.0.1:8000 index.php
```
5. To use custom function please refer to example. The Filename in "./dev" should be the same as the function name.

# Examples and tests

## Make sure to remove "TEST" header when using for your own mocks.json.

1. `curl --header "TEST: true" 127.0.0.1:8000/api/v1.0/test1`

```json
[123]
```

2. `curl --header "TEST: true" 127.0.0.1:8000/api/v1.0/megatesto2/myname/666`

```json
{
   "name": "myname",
   "id": "666",
   "some other variable": "My Little Test",
   "Test a function var": "I'd like to test a function."
}
```

# BUT WHY?????!!!
- I needed a mock server without all these overloaded fancy maze GUIs (hi Postman) that I'll be able to use for my app.
- Good if you could find a use for it too.
- I don't want to add it to `composer` as it should be more flexible than that. Just clone.

# TODO
- Custom handler functions (not properly tested yet)