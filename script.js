// Erase user input on page reload
window.onload = function () {
    let form = document.getElementById("insertForm");
    form.reset();
};

// Display "Add new project/student" dialog
function dialogOn() {
    document.getElementById("overlay").style.display = "block";
}

// Hide "Add new project/student" dialog and erase user input
function dialogOff() {
    document.getElementById("overlay").style.display = "none";
    let form = document.getElementById("insertForm");
    form.reset();
}

// Truncate project/student name to maximum allowed length
const target = document.getElementById("name");

function truncate(input) {
    if (input.length > 255) {
        target.value = input.substr(0, 255);
    }
};