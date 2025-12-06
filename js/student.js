// Join Course (Submit request for approval)
async function joinCourse(courseId) {
    if (!confirm('Request to join this course? You will need approval from faculty.')) return;
    
    try {
        const response = await fetch('../php/enroll_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'course_id=' + courseId
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Error requesting enrollment');
    }
}

// Mark Attendance
async function markAttendance(event, sessionId) {
    event.preventDefault();
    
    const pin = document.getElementById('pin_' + sessionId).value;
    const messageDiv = document.getElementById('message_' + sessionId);
    
    if (pin.length !== 4) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'PIN must be 4 digits';
        return;
    }
    
    try {
        const response = await fetch('../php/mark_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_id: sessionId,
                pin: pin
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = '✓ ' + result.message;
            setTimeout(() => location.reload(), 2000);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = '✗ ' + result.message;
        }
    } catch (error) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Error marking attendance';
    }
}