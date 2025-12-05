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
        const response = await fetch('../actions/create_session.php', {
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
        const response = await fetch('../actions/toggle_session.php', {
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
        const response = await fetch('../actions/toggle_session.php', {
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