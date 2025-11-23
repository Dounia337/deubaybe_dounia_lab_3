
    function joinCourse(courseId) {
        if (confirm('Are you sure you want to join this course?')) {
            const formData = new FormData();
            formData.append('course_id', courseId);

            fetch('enroll_course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error joining course');
            });
        }
    }