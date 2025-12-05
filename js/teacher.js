// Show/Hide Forms
function showCreateCourseForm() {
    document.getElementById('createCourseForm').style.display = 'block';
    document.getElementById('editCourseForm').style.display = 'none';
    document.getElementById('deleteCourseForm').style.display = 'none';
    document.getElementById('statisticsView').style.display = 'none';
}

function hideCreateCourseForm() {
    document.getElementById('createCourseForm').style.display = 'none';
}

function showEditCourseForm() {
    document.getElementById('editCourseForm').style.display = 'block';
    document.getElementById('createCourseForm').style.display = 'none';
    document.getElementById('deleteCourseForm').style.display = 'none';
    document.getElementById('statisticsView').style.display = 'none';
}

function hideEditCourseForm() {
    document.getElementById('editCourseForm').style.display = 'none';
}

function showDeleteCourseForm() {
    document.getElementById('deleteCourseForm').style.display = 'block';
    document.getElementById('createCourseForm').style.display = 'none';
    document.getElementById('editCourseForm').style.display = 'none';
    document.getElementById('statisticsView').style.display = 'none';
}

function hideDeleteCourseForm() {
    document.getElementById('deleteCourseForm').style.display = 'none';
}

function viewStatistics() {
    document.getElementById('statisticsView').style.display = 'block';
    document.getElementById('createCourseForm').style.display = 'none';
    document.getElementById('editCourseForm').style.display = 'none';
    document.getElementById('deleteCourseForm').style.display = 'none';
}

function hideStatistics() {
    document.getElementById('statisticsView').style.display = 'none';
}

// Load course data for editing
function loadCourseData() {
    const select = document.getElementById('edit_course_id');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('edit_course_name').value = option.getAttribute('data-name');
        document.getElementById('edit_semester').value = option.getAttribute('data-semester');
        document.getElementById('edit_cohort').value = option.getAttribute('data-cohort');
    }
}

// Create Course
async function createCourse(event) {
    event.preventDefault();
    
    const formData = {
        course_name: document.getElementById('course_name').value,
        semester: document.getElementById('semester').value,
        cohort: document.getElementById('cohort').value
    };
    
    try {
        const response = await fetch('../php/create_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('courseMessage');
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = result.message;
            document.getElementById('course_name').value = '';
            document.getElementById('semester').value = '';
            document.getElementById('cohort').value = '';
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        document.getElementById('courseMessage').textContent = 'Error creating course';
        document.getElementById('courseMessage').style.color = 'red';
    }
}

// Update Course
async function updateCourse(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('edit_course_id').value;
    
    if (!courseId) {
        alert('Please select a course');
        return;
    }
    
    const formData = {
        course_id: courseId,
        course_name: document.getElementById('edit_course_name').value,
        semester: document.getElementById('edit_semester').value,
        cohort: document.getElementById('edit_cohort').value
    };
    
    try {
        const response = await fetch('../php/update_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('editCourseMessage');
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = result.message;
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        document.getElementById('editCourseMessage').textContent = 'Error updating course';
        document.getElementById('editCourseMessage').style.color = 'red';
    }
}

// Delete Course
async function deleteCourse(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('delete_course_id').value;
    
    if (!courseId) {
        alert('Please select a course');
        return;
    }
    
    if (!confirm('Are you absolutely sure you want to delete this course? This action cannot be undone!')) {
        return;
    }
    
    try {
        const response = await fetch('../php/delete_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ course_id: courseId })
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('deleteCourseMessage');
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = result.message;
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        document.getElementById('deleteCourseMessage').textContent = 'Error deleting course';
        document.getElementById('deleteCourseMessage').style.color = 'red';
    }
}

// Load Course Statistics
async function loadCourseStats() {
    const courseId = document.getElementById('stats_course_id').value;
    const statsContent = document.getElementById('statsContent');
    
    if (!courseId) {
        statsContent.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch('../php/get_course_statistics.php?course_id=' + courseId);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            statsContent.innerHTML = `
                <div style="margin-top: 20px;">
                    <h5>Course Statistics</h5>
                    <table style="width: 100%; margin-top: 10px;">
                        <tr>
                            <th>Total Sessions</th>
                            <th>Total Students</th>
                            <th>Avg Attendance Rate</th>
                        </tr>
                        <tr>
                            <td>${data.total_sessions}</td>
                            <td>${data.total_students}</td>
                            <td>${data.avg_attendance}%</td>
                        </tr>
                    </table>
                </div>
            `;
        } else {
            statsContent.innerHTML = '<p style="color: red;">' + result.message + '</p>';
        }
    } catch (error) {
        statsContent.innerHTML = '<p style="color: red;">Error loading statistics</p>';
    }
}

// Assign Faculty Intern
async function assignFI(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('assign_course_id').value;
    const fiId = document.getElementById('assign_fi_id').value;
    
    try {
        const response = await fetch('../php/assign_fi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ course_id: courseId, fi_id: fiId })
        });
        
        const result = await response.json();
        const messageDiv = document.getElementById('assignMessage');
        
        if (result.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = result.message;
            document.getElementById('assign_course_id').value = '';
            document.getElementById('assign_fi_id').value = '';
            setTimeout(() => loadFIAssignments(), 1000);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = result.message;
        }
    } catch (error) {
        document.getElementById('assignMessage').textContent = 'Error assigning FI';
        document.getElementById('assignMessage').style.color = 'red';
    }
}

// Load FI Assignments
async function loadFIAssignments() {
    try {
        const response = await fetch('../php/get_fi_assignments.php');
        const result = await response.json();
        
        const listDiv = document.getElementById('fiAssignmentsList');
        
        if (result.success && result.data.length > 0) {
            let html = '<table style="width: 100%; margin-top: 10px;"><tr><th>Course</th><th>Faculty Intern</th><th>Assigned Date</th><th>Action</th></tr>';
            result.data.forEach(assignment => {
                html += `
                    <tr>
                        <td>${assignment.course_name}</td>
                        <td>${assignment.fi_name}</td>
                        <td>${assignment.assigned_at}</td>
                        <td><button onclick="removeFI(${assignment.id})" style="background: red;">Remove</button></td>
                    </tr>
                `;
            });
            html += '</table>';
            listDiv.innerHTML = html;
        } else {
            listDiv.innerHTML = '<p>No FI assignments yet.</p>';
        }
    } catch (error) {
        document.getElementById('fiAssignmentsList').innerHTML = '<p style="color: red;">Error loading assignments</p>';
    }
}

// Remove FI Assignment
async function removeFI(assignmentId) {
    if (!confirm('Remove this FI from the course?')) return;
    
    try {
        const response = await fetch('../php/remove_fi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ assignment_id: assignmentId })
        });
        
        const result = await response.json();
        if (result.success) {
            alert(result.message);
            loadFIAssignments();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error removing FI');
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