import json
import requests



# Constants
TASK_APP_HOST = '140.78.230.37'
TASK_APP_PORT = '8081'
TASK_APP_KEY = 'jobe-server-key'
HEADERS = {'Accept': 'application/json', 'X-API-KEY': TASK_APP_KEY}
SUBMIT_TIMEOUT = 60  # max. seconds for the submission id to be available
GRADING_TIMEOUT = 60  # max. seconds for the grading to be available


def construct_submission_data():
    """
    Constructs the submission data to send.
    """
    submission = """{{ STUDENT_ANSWER | e('py') }}""" # (triple quotes required as submission might stretch over multiple lines)
    return {"input" : submission}


# DO NOT MODIFY ANYTHING BELOW HERE
SEPARATOR = "#<ab@17943918#@>#"


def construct_submission_payload():
    """
    Constructs and returns the submission payload.
    """
    is_precheck = {{ IS_PRECHECK }}
    task_id = {{ TASK_ID }}
    user_id = "{{ STUDENT.username }}"
    assignment_id = {{ QUESTION.id }}
    language = "de"  # ??
    feedback_level = {{ FEEDBACK_LEVEL }}
    return {
        "taskId": task_id,
        "userId": user_id,
        "assignmentId": assignment_id,
        "language": language,
        "mode": "RUN" if is_precheck else "SUBMIT",
        "feedbackLevel": feedback_level,
        "submission": construct_submission_data()
    }


def send_submission(submission_payload):
    """
    Sends the submission to the task app.

    :param submission_payload: The submission payload to send.
    :return: The submission identifier.
    """
    url = f'http://{TASK_APP_HOST}:{TASK_APP_PORT}/api/submission?runInBackground=true'
    try:
        request_headers = {
            **HEADERS,
            'Content-Type': 'application/json'
        }
        response = requests.post(url, json=submission_payload, headers=request_headers, timeout=SUBMIT_TIMEOUT)
        response.raise_for_status()
      
        return response.text
    except requests.ConnectionError as ex:
        raise RuntimeError(
            f'Could not establish connection to task-app {url} when trying to send the submission. Message: {ex}')
    except requests.Timeout as ex:
        raise RuntimeError(
            f'Did not receive a response upon sending a submission to the task-app {url} within the timeout of {SUBMIT_TIMEOUT} seconds. Message: {ex}')
    except requests.HTTPError as ex:
        raise RuntimeError(
            f'Received an HTTP error from the task-app {url} while sending the submission. Message: {ex}')
    except Exception as ex:
        raise RuntimeError(f'An unexpected error occurred while sending the submission. Message: {ex}')


def fetch_grading(submission_id):
    """
    Fetches the grading from the task app.

    :param submission_id: The submission identifier.
    :return: The grading result.
    """
    url = f'http://{TASK_APP_HOST}:{TASK_APP_PORT}/api/submission/{submission_id}/result'
    is_precheck = {{ IS_PRECHECK }}
    if is_precheck:
        url += '?delete=true'
    try:
        request_headers = {
            **HEADERS,
            'X-API-TIMEOUT': f'{GRADING_TIMEOUT - 2}'
        }
        response = requests.get(url, headers=request_headers, timeout=GRADING_TIMEOUT)
        response.raise_for_status()
        return response.json()
    except requests.ConnectionError as ex:
        raise RuntimeError(
            f'Could not establish connection to task-app {url} when trying to fetch the grading. Message: {ex}')
    except requests.Timeout as ex:
        raise RuntimeError(
            f'Did not receive a response upon fetching the grading from the task-app {url} within the timeout of {GRADING_TIMEOUT} seconds. Message: {ex}')
    except requests.HTTPError as ex:
        raise RuntimeError(
            f'Received an HTTP error from the task-app {url} while fetching the grading. Message: {ex}')
    except Exception as ex:
        raise RuntimeError(f'An unexpected error occurred while fetching the grading. Message: {ex}')


def construct_feedback(grading):
    """
    Constructs the feedback object.
    :param grading: The grading information.
    """

    is_precheck = {{ IS_PRECHECK }}
    checks_passed = True
    #Table generation for feedback
    test_results = [["Test","Feedback","Result"]]
    for c in grading['criteria']:
        test_results.append([
            c['name'],
            c['feedback'],
            c['passed']
        ])
        if c['passed']== 0:
            checks_passed = False
    #if all prechecks passed sets the mark to 1 to get a white message output
    if is_precheck and checks_passed:
        mark = 1
    else:
        mark = grading['points'] / grading['maxPoints']

    criteria = {
        'fraction': mark,
        'testresults': test_results,
        'prologuehtml': grading['generalFeedback']
    }
    
    

    return criteria

def get_custom_error_feedback(reason):
    """
    Returns the error in the required format.
    """
    return [{
        'fraction': 0,
        'got': reason,
        'criterion': 'N/A',
        'iscorrect': False
    }]


def main():
    """
    Submits the student answer and prepares the grading result for CodeRunner.
    """
    try:
        payload = construct_submission_payload()
        submission_id = send_submission(payload)
        grading = fetch_grading(submission_id)
        feedback = construct_feedback(grading)

        # Include the result_columns in the final feedback output
        

        print(json.dumps(feedback))
        
        
    except Exception as ex:
        print_feedback(get_custom_error_feedback(f'An exception occurred: {ex}'))


if __name__ == "__main__":
    main()

