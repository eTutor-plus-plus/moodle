import json
import requests
import os

DISPATCHER_HOST = '127.0.0.1'
DISPATCHER_PORT = '8081'
HEADERS = {'Content-Type': 'application/json'}

def printFeedback(feedback):
    print(json.dumps(feedback))

def getCustomErrorFeedback(reason):
    return {
        'fraction': 0,
        'result': reason,
        'criterion': 'N/A'
    }
    
def construct_submission_payload(action, submission, exercise_id, diagnose_level=0, task_type='sql'):
    """Construct and return the submission payload."""
    return {
        'taskType': task_type,
        'exerciseId': exercise_id,
        'passedAttributes': {
            'action': action,
            'submission': submission,
            'diagnoseLevel': diagnose_level
        },
        'passedParameters': {}
    }

def send_submission(submission_payload, timeout=3):
    """Send the submission and return the submission ID."""
    submission_url = f'http://{DISPATCHER_HOST}:{DISPATCHER_PORT}/submission?persist=false'
    try:
        response = requests.post(submission_url, json=submission_payload, headers=HEADERS, timeout=timeout)
        response.raise_for_status()
        return response.json()['submissionId']
    except requests.ConnectionError as e:
        raise RuntimeError(f'Could not establish connection to dke-dispatcher within the specified timeout of {timeout} seconds when trying to send the submission. Message: {e}')
    except requests.Timeout as e:
        raise RuntimeError(f'Did not receive a response upon sending a submission to the dke-dispatcher within the specified timeout of {timeout} seconds. Message: {e}')    
    except requests.HTTPError as e:
        raise RuntimeError('Received an HTTP error from the dke-dispatcher while sending the submission. Message: {e}')
    except Exception as e:
        raise RuntimeError(f'An unexpected error occurred while sending the submission. Message: {e}')

def fetch_grading(submission_id, timeout=5):
    """Fetch the grading for a given submission ID and return grading data."""
    grading_url = f'http://{DISPATCHER_HOST}:{DISPATCHER_PORT}/grading/{submission_id}?delete=true'
    try:
        grading_response = requests.get(grading_url, timeout=timeout)
        grading_response.raise_for_status()
        return grading_response.json()
    except requests.ConnectionError as e:
        raise RuntimeError(f'Could not establish connection to dke-dispatcher within the specified timeout of {timeout} seconds when trying to fetch the grading for the submission {submission_id}.  Message: {e}')
    except requests.Timeout as e:
        raise RuntimeError(f'Did not receive a response when trying to fetch a grading for id {submission_id} from the dke-dispatcher within the specified timeout of {timeout} seconds.  Message: {e}')  
    except requests.HTTPError as e:
        raise RuntimeError('Received an HTTP error from the dke-dispatcher while fetching the grading.  Message: {e}')
    except Exception as e:
        raise RuntimeError(f'An unexpected error occurred while fetching the grading. Message: {e}')

def construct_feedback(action, grading_data):
    """Construct and return feedback based on grading data."""
    result = grading_data['result']
    submission_suits_solution = grading_data['submissionSuitsSolution']
    mark = 1 if submission_suits_solution else 0
    criterion = 'No Syntax Errors' if action == 'run' else 'Correct Result'

    return {
        'fraction': mark,
        'result': result,
        'criterion': criterion
    }


def main():
    try:
        action = {{ TEST.stdin }}
        submission = """{{ STUDENT_ANSWER | e('py') }}"""
        exercise_id = {{ EXERCISE_ID }}
    
        submission_payload = construct_submission_payload(action, submission, exercise_id)
        submission_id = send_submission(submission_payload)
        grading_data = fetch_grading(submission_id)
        feedback = construct_feedback(action, grading_data)
    
        printFeedback(feedback)
    except Exception as e:
        printFeedback(getCustomErrorFeedback(f'An exception occured: {e}'))

if __name__ == "__main__":
    main()
