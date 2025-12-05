// Create Session
document.getElementById('createSessionForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        course_id: document.getElementById('session_course_id').value,
        session_date: document.getElementById('session_date').value,
        session_time: document.getElementById('session_time').value,
        hall: document.getElementById('hall').value,
        duration: document.getElementById('duration').value,
        session_pin: document.getElementById('session_pin').value
    };
    
    try {
        const response = await fetch('../php/create_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('sessionMessage');
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = result.message;
            document.getElementById('createSessionForm').reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        document.getElementById('sessionMessage').textContent = 'Error creating session';
        document.getElementById('sessionMessage').style.color = 'red';
    }
});

// Activate Session
async function activateSession(sessionId) {
    if (!confirm('Activate this session for attendance?')) return;
    
    try {
        const response = await fetch('../php/toggle_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ session_id: sessionId, action: 'activate' })
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error activating session');
    }
}

// Deactivate Session
async function deactivateSession(sessionId) {
    if (!confirm('Deactivate this session?')) return;
    
    try {
        const response = await fetch('../php/toggle_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ session_id: sessionId, action: 'deactivate' })
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error deactivating session');
    }
}

// Approve Enrollment
async function approveEnrollment(enrollmentId) {
    try {
        const response = await fetch('../php/manage_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enrollment_id: enrollmentId, action: 'approve' })
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error approving enrollment');
    }
}

// Reject Enrollment
async function rejectEnrollment(enrollmentId) {
    if (!confirm('Reject this enrollment request?')) return;
    
    try {
        const response = await fetch('../php/manage_enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enrollment_id: enrollmentId, action: 'reject' })
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error rejecting enrollment');
    }
}

// Load Course Report
async function loadCourseReport() {
    const courseId = document.getElementById('report_course_id').value;
    const reportContent = document.getElementById('reportContent');
    
    if (!courseId) {
        reportContent.innerHTML = '<p>Please select a course to view reports.</p>';
        return;
    }
    
    try {
        const response = await fetch('../php/get_course_statistics.php?course_id=' + courseId);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            reportContent.innerHTML = `
                <div>
                    <p><h4>Attendance Reports</h4></p>
                    <table>
                        <tr>
                            <th style="color: rgb(172, 80, 80);">Total Sessions</th>
                            <th style="color: rgb(172, 80, 80);">Total Students</th>
                            <th style="color: rgb(172, 80, 80);">Present</th>
                            <th style="color: rgb(172, 80, 80);">Late</th>
                            <th style="color: rgb(172, 80, 80);">Avg Attendance</th>
                        </tr>
                        <tr>
                            <td>${data.total_sessions}</td>
                            <td>${data.total_students}</td>
                            <td>${data.present_count}</td>
                            <td>${data.late_count}</td>
                            <td>${data.avg_attendance}%</td>
                        </tr>
                    </table>
                </div>
            `;
        } else {
            reportContent.innerHTML = '<p style="color: red;">' + result.message + '</p>';
        }
    } catch (error) {
        reportContent.innerHTML = '<p style="color: red;">Error loading report</p>';
    }
}