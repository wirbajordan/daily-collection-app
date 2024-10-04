function deconnexion() {
    var ub = confirm("vous serez d\351connect\351 apres\350s confirmation!!!");
    if (ub == true) {
        window.open("login.php?locks", "_self", false);
    } else {
        alert('D\351connexion annule\351');
    }
}

function openNav() {
    document.getElementById("mySidebar").style.width = "250px";
    document.getElementById("main").style.marginLeft = "250px";
    document.getElementById("main-content").style.marginLeft = "250px";
    document.getElementById("main").style.display = "none";
}

function closeNav() {
    document.getElementById("mySidebar").style.width = "0";
    document.getElementById("main").style.marginLeft = "0";
    document.getElementById("main").style.display = "block";
}


