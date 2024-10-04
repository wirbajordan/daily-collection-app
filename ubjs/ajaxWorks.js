function viewCourses() {
    $.ajax({
        url: "./adminView/viewCourse.php",
        method: "post",
        data: {record: 1},
        success: function (data) {
            $('.allContent-section').html(data);
        }
    });
}

function showCategory() {
    $.ajax({
        url: "./adminView/viewCategories.php",
        method: "post",
        data: {record: 1},
        success: function (data) {
            $('.allContent-section').html(data);
        }
    });
}


