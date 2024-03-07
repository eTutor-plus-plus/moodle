# Create Task

In order for tasks to be created, a prototype for the task-type has to be created manually.

## Task Prototype

Create a new Code-Runner Question and set the following values:

| Area                     | Field             | Subfield               | Value                                                             |
| ------------------------ | ----------------- | ---------------------- | ----------------------------------------------------------------- |
| Coderunner question type | Question type     |                        | python3                                                           |
| Coderunner question type | Customisation     | Customise              | activate                                                          |
| Coderunner question type | Submit buttons    | Precheck               | Selected                                                          |
| Coderunner question type | Submit buttons    | Hide check             | activate                                                          |
| Coderunner question type | Feedback          |                        | Force show                                                        |
| Coderunner question type | Marking           | All-or-nothing-grading | activate                                                          |
| Customisation            | Template          |                        | See file `./jobe/template.py`                                     |
| Customisation            | Template Controls | Is combinator          | activate                                                          |
| Customisation            | Template Controls | Allow multiple stdins  | activate                                                          |
| Customisation            | Grading           |                        | Template grader                                                   |
| Customisation            | Result columns    |                        | _empty_                                                           |
| Advanced customisation   | Prototyping       | Is prototype?          | Yes (user defined)                                                |
| Advanced customisation   | Prototyping       | Question type          | e.g. `etutor-sql`                                                 |
| Advanced customisation   | Languages         | Sandbox language       | python3                                                           |
| General                  | Category          |                        | ETUTOR_PROTOTYPES                                                 |
| General                  | Question name     |                        | e.g. `ETUTOR_PROTOTYPE_SQL`                                       |
| General                  | Question text     |                        | Specify which template parameters can be defined in the question. |
| General                  | Question status   |                        | Ready                                                             |

## Task

Create a new Code-Runner Question and set the following values:

| Area                     | Field                | Subfield               | Value                                        |
| ------------------------ | -------------------- | ---------------------- | -------------------------------------------- |
| Coderunner question type | Question type        |                        | e.g. `etutor-sql`                            |
| Coderunner question type | Submit buttons       | Precheck               | Selected                                     |
| Coderunner question type | Submit buttons       | Hide check             | activate                                     |
| Coderunner question type | Feedback             |                        | Force show                                   |
| Coderunner question type | Marking              | All-or-nothing-grading | activate                                     |
| Coderunner question type | Template params      |                        | `{"TASK_ID": <ID>, "FEEDBACK_LEVEL": <0-3>}` |
| General                  | Category             |                        | Your category                                |
| General                  | Question name        |                        | Question name                                |
| General                  | Question text        |                        | Question text                                |
| General                  | Question status      |                        | Ready                                        |
| Answer                   | Answer               |                        | Optionally, enter the solution               |
| Answer                   | Validate on Save     |                        | Deactivate, when no solution is entered      |
| Test cases               | Test case 1          |                        | DIAGNOSE (for assignment) / RUN (for exam)   |
| Test cases               | Test properties 1    | Display                | Show                                         |
| Test cases               | Precheck test type 1 |                        | Precheck only                                |
| Test cases               | Test case 2          |                        | SUBMIT                                       |
| Test cases               | Test properties 2    | Display                | Hide                                         |
| Test cases               | Precheck test type 2 |                        | Check only                                   |
