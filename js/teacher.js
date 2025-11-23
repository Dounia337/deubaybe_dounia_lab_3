
    function showCreateCourseForm() {
        document.getElementById('createCourseForm').style.display = 'block';
    }

    function hideCreateCourseForm() {
        document.getElementById('createCourseForm').style.display = 'none';
    }

    function createCourse(event) {
        event.preventDefault();
        
        const courseData = {
            course_name: document.getElementById('course_name').value,
            semester: document.getElementById('semester').value,
            cohort: document.getElementById('cohort').value
        };

        fetch('create_course.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(courseData)
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('courseMessage');
            if (data.success) {
                messageDiv.style.color = 'green';
                messageDiv.innerHTML = data.message;
                setTimeout(() => location.reload(), 2000);
            } else {
                messageDiv.style.color = 'red';
                messageDiv.innerHTML = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('courseMessage').style.color = 'red';
            document.getElementById('courseMessage').innerHTML = 'Error creating course';
        });
    }