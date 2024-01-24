Task:
1. Task Prototyp
    - select "python3" as question type
    - create new coderunner question
    - activate "customise"
    - Submit buttons: Precheck: "Selected" and activate "Hide check"
    - Feedback "Force show"
    - check "All-or-nothing grading"
    - Customisation: Template has to match `template.py`
    - Template controls: activate "Is combinator" and "Allow miltiple stdins"
    - Grading needs to be "Template grader"
    - "Result columns" field needs to be empty
    - Student answers: "Ace" and activate "Template uses ace"
    - Advanced customisation: Is prototype? is "Yes (user defined)" and Question type is "your questiontype"
    - Sandbox lanquage is "python3"
    - Name and Description for the Question
    - Questions status needs to be "ready"
    - Create Prototype
2. Task
    - "prototype_name" as "question type" 
    - Submit buttons: Precheck Selected and activate Hide check
    - Template params need the TASK_ID and FEEDBACK_LEVEL in following json format: 
{
"TASK_ID": 2,
"FEEDBACK_LEVEL":3
}
    - "Hoist template parameters" needs to be activated
    - Name and Description for the Question
    - Testcase 1 needs to be named "DIAGNOSE" for assignments and "RUN" for Tests
    - Testcase 1:"Display" is "Show" and "Precheck test type" needs to be "Precheck only"
    - Testcas 2 needs to be named ""SUBMIT""
    - Testcase 2: Display" is "Hide" and "Precheck test type" needs to be "Check only"
    - Save 

