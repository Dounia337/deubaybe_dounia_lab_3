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
        const response = await fetch('../actions/create_course.php', {
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
        const response = await fetch('../actions/update_course.php', {
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
        const response = await fetch('../actions/delete_course.php', {
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
        const response = await fetch('../actions/get_course_stats.php?course_id=' + courseId);
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