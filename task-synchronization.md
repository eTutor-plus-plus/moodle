# Task Synchronization

Following settings must be made to the Moodle installation for the eTutor Task Synchronization to work:

## Moodle Configuration

Execute following steps as Moodle administrator in the Moodle System:

1. **Enable web services**: Activate "Enable web services" in  _Site Administration > General > Advanced Features_
2. **Enable REST protocol**: Activate "REST protocol" in _Site Administration > Server > Web services > Manage
   Protocols_
3. **Create REST user**: Create a user that is allowed to call the REST endpoints.
    1. **Add a new role** in  _Site Administration > Users > Permissions > Define roles_
        * _Use role or archetype__: No role
        * _Short name_: web_service
        * _Custom full name_: Web Service
        * _Custom description_: Web Service for eTutor Task Administration
        * _Role archetype_: None
        * _Context types where this role may be assigned_: System
        * _Allow role assignments_: Nothing
        * _Allow role overrides_: Nothing
        * _Allow role switches_: Nothing
        * _Allow role to view_: Nothing
        * _Capabilities_: Allow
            - webservice/rest:use
            - moodle/category:manage
            - moodle/category:viewhiddencategories
            - moodle/question:managecategory
            - moodle/question:add
            - moodle/question:editall
            - moodle/question:moveall
            - moodle/question:tagall
            - moodle/question:viewall
            - qbank/customfields:changelockedcustomfields
            - qbank/customfields:viewhiddencustomfields
    2. **Create user** in  _Site Administration > Users > Accounts > Add a new user_
        * _Username_: etutor_sync
        * _Choose an authentication method_: Manual account
        * _Password_: some secure password
        * _Force password change_: false
        * _First name_: Sync
        * _Last name_: eTutor
        * _Email address_: a unique, working email address
        * _Email visibility_: Hidden
    3. **Add user to role** in _Site Administration > Users > Permissions > Assign system roles_
        * Assign the previously created `etutor_sync` user to the previously created `web_service` role.
4. **Add custom external service** in _Site Administration > Server > Web services > External services_
    1. **Add** service
        * _Name:_ eTutor Sync
        * _Short Name:_ etutor_sync
        * _Enabled_: true
        * _Authorised users only_: true
    2. **Add functions** in _Site Administration > Server > Web services > External services > Functions_
        * core_course_create_categories
        * core_course_get_categories
        * core_course_update_categories
        * local_etutorsync_create_question_category
        * local_etutorsync_update_question_category
    3. **Add user** in _Site Administration > Server > Web services > External services > Authorised users_
        * Add the previously created `etutor_sync` user.
5. **Configure question sync service**
    1. TBD
6. **Configure custom question fields** in _Site Administration > Plugins > Question bank plugins > Question custom
   fields_
    1. **Add a new category** with name eTutor
    2. **Add a new custom field - Short text**
        * _Name_: Task-ID
        * _Short name_: etutor_task_id
        * _Description_: The eTutor task id.
        * _Required_: No
        * _Unique data_: Yes
        * _Locked_: Yes
        * _Visible to_: Teachers
    2. **Add a new custom field - Date and time**
        * _Name_: Last Modification
        * _Short name_: etutor_last_modification
        * _Description_: The timestamp the task was last modified in the eTutor system.
        * _Required_: No
        * _Unique data_: No
        * _Include time_: Yes
        * _Locked_: Yes
        * _Visible to_: Teachers

## Task-Administration Configuration

1. In the Moodle System create a web service token. As administrator open _Site Administration > Server > Web services >
   Manage tokens_
    * _Name_: etutor_task_administration
    * _User_: The previously created `etutor_sync` user.
    * _Service_: The previously created custom web service `etutor_sync`.
    * _IP restriction_: enter if required
    * _Valid until_: set as required
2. Set following settings in the applications' `application-[dev|prod].yml` file or the corresponding environment
   variable.
    * `moodle.token`: The token created in the previous step.
    * `moodle.url`: The URL to the moodle server (e.g. `http://localhost:8000/`).