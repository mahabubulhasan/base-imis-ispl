Language Setting

Tables

languages

translates

The corresponding tables have their respective models that are named in Pascal Case in singular form. Hotspots model is located at app\\Models\\Language\\.

\### Views

All views used by this module is stored in resources\\views\\public-health\\water-samples

\- language.index: lists language records.

\- language.create: opens form and calls partial-form for form contents

\- language.partial-form: creates form content

\- language.edit: opens form and calls partial-form for form contents

\- language.show: displays all attributes of particular record

\- language.add\_translations : opens form for language translation

\- language.import : contains a option to upload translated words for each language

Language Controller

app\Http\Controllers\Language\LanguageController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|Function|Index()|
| :- | :- |
|Description|Retrieves all available languages from the `Language` model and returns the `language.index` view with the languages and page title.|
|Parameter|None|
|Return|View (`language.index`) with languages and page title  |
|Source|`app\Http\Controllers\LanguageController.php`  |
|Logic|` `This function fetches all language records from the database.Sets a translated page title.Returns a Blade view (language.index) with the retrieved data.|
|Remarks|None|


|Function|generate\_translate($code)|
| :- | :- |
|Description|Generates a translation file for a specified language code (`$code`).  |
|Parameter|Redirect with success or error messages  |
|Return||
|Logic|This function generates a JSON translation file for the given language code by first verifying if the language exists using is\_lang\_exist($lang). It then retrieves English translations using get\_translation('en', false), iterates over them, and attempts to find corresponding translations in the target language with get\_by\_key($key, $lang). Each translation is stored in an array where the key is either the original key or text, and the value is the translated text. The function then checks if the system has permission to write files by creating and deleting a test file. If writable, it encodes the translations into JSON format and calls generate\_lang\_file($lang, $content, 'update') to store them. Based on the success or failure of file creation, it redirects the user with either a success or error message.|
|Source|app\Http\Controllers\LanguageController.php|

|Function|get\_translation($lang = 'base', $only = true)|
| :- | :- |
|Description|Fetches translations for a specific language|
|Parameter|`$lang`, `$only`  |
|Return|Translation data (JSON or array)  |
|Source|`	`app\Http\Controllers\LanguageController.php|
|Logic|This function fetches translations for a given language from the Translate table. It determines which columns to retrieve based on the $only parameter—fetching only essential fields (key, name, text, load) if $only is true, or additional metadata (pages, group, platform) if false. It then queries the Translate model for entries matching the language name and returns the results.|
|Remarks|Returns only key, name, text, and load fields if `$only` is `true`. Otherwise, fetches additional metadata like pages, group, and platform|


|Function|get\_by\_key($key, $lang = 'base')|
| :- | :- |
|Description|Fetches a single translation by key and language|
|Parameter|`$key`, `$lang`  |
|Return|Translation text  |
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function retrieves a single translation from the Translate table based on a specific key and language. It queries the database where the key matches the provided value and the name matches the language code. If a matching record exists, it returns the translation entry; otherwise, it returns null.|
|Remarks|None|


|Function|is\_lang\_exist($lang, $column = 'code')|
| :- | :- |
|Description|Checks if a language exists in the `Language` table based on a specified column|
|Parameter|`$lang`, `$column`|
|Return|Boolean (`true` if exists, `false` otherwise)  |
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function checks whether a language exists in the Language table by querying it based on the provided column (defaulting to code). If a matching record is found, it returns true; otherwise, it returns false. This ensures that translation files are only generated for valid languages.|
|Remarks|None|



|Function|generate\_lang\_file($lang, $content, $action = 'update')|
| :- | :- |
|Description|Creates or updates a JSON translation file for a language|
|Parameter|`$lang`, `$content`, `$action`|
|Return|Success or failure status  |
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function creates or updates a JSON translation file for a specific language. If the $action is store, it first checks if a file already exists and deletes it before writing new content. If updating, it verifies that the language directory is writable, then deletes and replaces the file with the new content. If successful, it returns an object indicating success; otherwise, it returns an object with a failure status.|
|Remarks|Deletes existing files before writing new content and ensures file permissions allow writing|

|Function|set\_lang(Request $request)|
| :- | :- |
|Description|Sets the application language by storing the selected language in a cookie|
|Parameter|$request|
|Return|Redirect response|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function sets the application's language preference by retrieving the selected language from the request ($request->lang, defaulting to 'en') and storing it in a cookie named app\_language. The cookie is set to expire after one year, ensuring that users retain their language selection even after closing the browser. After setting the cookie, the function redirects the user back to the previous page.|
|Remarks|None|



|Function|getData(Request $request)|
| :- | :- |
|Description|Fetches and formats language data for DataTables|
|Parameter|`$request`|
|Return|JSON response for DataTables  |
|Source|app\Http\Controllers\LanguageController.php	|
|Logic|This function retrieves all languages that haven’t been soft-deleted (deleted\_at IS NULL), orders them by ID, and returns a DataTables response. It includes action buttons for editing, viewing, adding translations, importing translations, generating language files, and deleting a language. Each button is displayed based on the user’s permissions, and a status column is modified to show either "Active" or "Disabled" based on the language status.|
|Remarks|Adds action buttons for edit, view, add translation, import, and delete|



|Function|create()|
| :- | :- |
|Description|Returns the `language.create` view for adding a new language|
|Parameter|None|
|Return|View (`language.create`)|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function prepares the view for adding a new language. It sets the page title and returns the language.create Blade view.|
|Remarks|None|




|Function|create\_import($id)|
| :- | :- |
|Description|Returns the `language.import` view for importing translations for a specific language|
|Parameter|`$id`|
|Return|View (`language.import`)|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function loads the page for importing translations for a specific language. It sets the page title and passes the language ID to the language.import view.|
|Remarks|None|




|Function|store(LanguageRequest $request)|
| :- | :- |
|Description|Stores a new language in the database|
|Parameter|`$request`|
|Return|Redirect with success or error messages|
|Logic|This function handles the creation of a new language. It starts a database transaction, extracts form data, and calls storeOrUpdate(null, $data). After creating the language, it ensures that translation keys from the English language are copied to the new language. It then resets the sequence for the translates table to maintain proper ordering and inserts translations for the new language. If successful, the transaction is committed, and the user is redirected with a success message; otherwise, it logs the error, rolls back, and returns an error response.|
|Source|app\Http\Controllers\LanguageController.php|
|Remarks|Starts a database transaction, inserts a new language, copies English translations, and commits the transaction or rolls back on error|



|Function|storeOrUpdate($id, $data)|
| :- | :- |
|Description|Stores or updates a language.  |
|Parameter|`$id`, `$data`  |
|Return|Success or failure status  |
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function either creates a new language or updates an existing one. If an id is provided, it fetches the existing language and updates its code in the Translate table. The function then updates or sets values like name, label, code, and status, and saves the record. It returns the language ID after saving.|
|Remarks|If `$id` is `null`, creates a new language. Updates existing translations when modifying a language.|


|Function|show($id)|
| :- | :- |
|Description|Displays the details of a specific language|
|Parameter|'$id'|
|Return|View (language.show)|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function retrieves a language by its ID and displays its details on the language.show page. If the language doesn’t exist, it triggers a 404 error.|
|Remarks|Returns a 404 error if the language is not found.|


|Function|edit($id)|
| :- | :- |
|Description|Returns the language.edit view for editing a specific language.|
|Parameter|'$id'|
|Return|View (language.edit)|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function retrieves a language for editing and passes it to the language.edit view along with the page title.|
|Remarks|None|


|Function|update(LanguageRequest $request, $id)|
| :- | :- |
|Description|Updates a specific language with the provided data.|
|Parameter|$request *(LanguageRequest)*|
|Return|'$id'|
|Source|Redirects to language/setup with a success or error message.|
|Logic|This function updates an existing language. It fetches the language by its ID, retrieves the form data, and calls storeOrUpdate($id, $data). If the language exists, it redirects with a success message; otherwise, it redirects with an error message.|
|Remarks|Calls the storeOrUpdate method for updating the language.|


|Function|destroy($id)|
| :- | :- |
|Description|Deletes a specific language and its related translations.|
|Parameter|`$id`|
|Return|Redirects back with a success or error message.|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function deletes a language and its associated translations. It starts a database transaction, finds the language, deletes its translations, and then deletes the language itself. If successful, the transaction is committed and a success message is returned; otherwise, an error is logged, the transaction is rolled back, and an error message is displayed.|
|Remarks|Uses transactions to ensure atomicity.	|


|Function|add\_translation($languageId)|
| :- | :- |
|Description|Returns the language.add\_translation view for adding translations for a specific language|
|Parameter|`$languageId`|
|Return|View (language.add\_translation)|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function retrieves the source translations for the English language from the database and groups them by pages, sorting them alphabetically by their text within each page. It then retrieves the existing translations for the specified target language. Finally, the function prepares a view to display both the source translations and the existing translations for the specified language, allowing the user to add or modify translations.|
|Remarks|Fetches existing English translations and organizes them by pages.|


|Function|saveStepTranslation(Request $request, $languageId)|
| :- | :- |
|Description|Saves or updates translations in bulk for a specific language.|
|Parameter|` $request`,` $languageId `|
|Return|JSON response with success or error messages.|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|The function begins by validating the user-submitted translations to ensure they are in the correct format. It then fetches the existing translations for the given language from the database. The translations are compared to see if there are any changes. If a translation needs to be updated (i.e., the new translation differs from the stored one), it is marked for update. If the translation is new, it is marked for insertion. Once the updates and insertions are prepared, they are executed in batches to optimize database operations. The function ensures data consistency using a database transaction and returns a success or failure message based on the operation's result.|
|Remarks|Handles batch insertions and updates to optimize performance.|


|Function|getPageFromKey($key)|
| :- | :- |
|Description|Extracts the page name from a translation key.|
|Parameter|`$key'`|
|Return|The page name.|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function extracts the page name from the translation key by splitting the key using a dot (.) and returning the first part. If the key does not follow the expected format, it defaults to a 'general' page name.|
|Remarks|Defaults to "general" if no page is found.|


|Function|import\_translates($id, Request $request)|
| :- | :- |
|Description|Handles the import of translations from a CSV file for a specific language.|
|Parameter|`$id`,`$request`|
|Return|Redirects to language/setup with a success or error message.|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function handles the import of translations from a CSV file. It first validates the file format and ensures the CSV contains the required columns (key, text, and translated\_text). The file is then processed to extract the translation data, and any existing translations that have changed are updated in the database. New translations are ignored if the text is unchanged. The process is optimized by updating records in chunks. Upon completion, a success message is displayed.|
|Remarks|Validates CSV format and updates translations in batches.|


|Function|export\_csv\_format()|
| :- | :- |
|Description|exports a CSV template with existing English translation keys for translation.|
|Parameter|None|
|Return|Downloads a CSV file named "Translation Format.csv"|
|Source|app\Http\Controllers\LanguageController.php|
|Logic|This function generates a CSV template for importing translations. It first retrieves the English language translations and creates a CSV file with the necessary headers (key, text, translated\_text). The translations are added to the CSV file in chunks to optimize performance. The generated CSV is then sent to the browser for download, allowing users to easily import translations in the required format.|
|Remarks|Formats the file with column headers (key, text, translated\_text).|


LanguageRequest

Location: app\\Http\\Requests\\Language\\LanguageRequest.php

LanguageRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

|Function|authorize|
| :- | :- |
|Description|Determines if user is authenticated or not|
|Parameters||
|Return|Returns true|
|Source|app\\Http\\Requests\\Language\\LanguageRequest.php|


|Function|message()|
| :- | :- |
|Description|Message to be displayed in case of validation error.|
|Parameter||
|Return|Return validation message|
|Source|app\\Http\\Requests\\Language\\LanguageRequest.php|
|Logic|This function provides custom error messages for validation failures. It ensures that when a validation rule is not met, a user-friendly message is returned instead of a generic system error. For example, if the name field is missing, the error message will be "Language Name required." Similarly, it defines custom messages for missing or incorrect code values, including uniqueness and regex validation.|
|Remarks|Need to include errors.list.blade that displays the error message in dashboard Format: ‘Field.validation\_rule’ =\>”error\_message”|

|Function|rules() |
| :- | :- |
|Description|Contains the validation rules|
|Parameter||
|Return|Return validation logic to calling place on the basis of request method|
|Source|app\\Http\\Requests\\Language\\LanguageRequest.php|
|Logic|This function dynamically returns the validation rules based on the HTTP method used for the request. If the request method is POST, it calls the store() function to get the validation rules for creating a new entry. Otherwise, it calls the update() function to apply the rules for updating an existing entry.|
|Remarks|Format for validation rule: ‘field\_name’=\>’validation\_rule1 \| validation\_rule2’|

|Function|store () |
| :- | :- |
|Description|Contains the validation rules for store function |
|Parameter||
|Return|Return validation for field in store form|
|Source|app\\Http\\Requests\\Language\\LanguageRequest.php|
|Logic|This function defines the validation rules for storing a new language. The name and status fields are required, while the code field must be a string containing only letters (a-z or A-Z), up to four characters long, and must be unique within the code column of the languages table in the pgsql.language schema.|
|Remarks||


|Function|update () |
| :- | :- |
|Description|Contains the validation rules for update function |
|Parameter||
|Return|Return validation for field in update form|
|Source|app\\Http\\Requests\\Language\\LanguageRequest.php|
|Logic|This function provides the validation rules for updating an existing language entry. It has the same validation conditions as the store() function, meaning that name and status are required, and code must follow the same format and uniqueness constraint. However, in practical scenarios, the uniqueness validation should often exclude the current record’s code when updating to prevent conflicts with itself.|
|Remarks||



















Models

Location: app\Models\Language\Language.php

This Language model represents the language.languages table in the database, defining relationships and mass-assignable attributes.

The models contain the connection between the model and the table defined by

\$table = ‘language. languages’ as well as the primary key defined by

primaryKey= ‘id’

|Function|translate()|
| :- | :- |
|Description|Defines a one-to-many relationship between Language and Translate.|
|Parameter|None|
|Return|Returns a collection of related Translate records.|
|Source|app\Models\Language\Language.php|
|Logic|This function establishes a hasMany relationship where one Language entry can have multiple Translate entries. However, it incorrectly maps the name field in Language to the code field in Translate, which likely needs correction.|
|Remarks|Ensure that the foreign key (name) and local key (code) correctly reference existing columns in the Translate model.|

Location: app\Models\Language\Translate.php

The models contain the connection between the model and the table defined by

\$table = ‘language. translates as well as the primary key defined by

primaryKey= ‘id’

























AppServiceProvider

Location: app\Providers\AppServiceProvider.php

This AppServiceProvider forces HTTPS in production and sets the application locale based on a decrypted language preference stored in a cookie.

|Function|boot() |
| :- | :- |
|Description|Bootstraps application services when the app starts.|
|Parameter|None|
|Return|No direct return, but it configures the application’s behavior.|
|Source|<p>app\Providers\AppServiceProvider.php</p><p></p>|
|Logic|This function ensures that if the application is running in a production environment (APP\_ENV=production), it forces all URLs to use HTTPS for security. Additionally, it calls the setAppLocale() function to set the application's language based on the user’s selected preference stored in a cookie.|
|Remarks|Ensures secure HTTPS connections in production and dynamically sets the locale based on user preferences.|


|Function|setAppLocale () |
| :- | :- |
|Description|Sets the application's language based on a saved cookie or defaults to English.|
|Parameter|None|
|Return|No direct return, but it updates the application's locale setting.|
|Source|<p>app\Providers\AppServiceProvider.php</p><p></p>|
|Logic|The function first sets the default locale to English (en). If a language preference is stored in a cookie (app\_language), it attempts to decrypt it. The decrypted value is split using the | character, and the second part of the split value is assigned as the locale. If decryption fails, the exception is caught and ignored to prevent application crashes. Finally, the determined locale is applied using App::setLocale($locale), ensuring the application operates in the selected language.|
|Remarks|Ensures that the application supports multilingual functionality by allowing users to select their preferred language, with English as the default.|


