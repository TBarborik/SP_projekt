set_dateTime();
setInterval(set_dateTime, 1000);

function set_dateTime()
{
    var elementDate = document.getElementById("date")
    var elementTime = document.getElementById("time");
    var dateTime    = new Date();

    var hodiny = dateTime.getHours() < 10 ? "0"+dateTime.getHours() : dateTime.getHours();
    var minuty = dateTime.getMinutes() < 10 ? "0"+dateTime.getMinutes() : dateTime.getMinutes();
    var sekundy = dateTime.getSeconds() < 10 ? "0"+dateTime.getSeconds() : dateTime.getSeconds();

    elementDate.innerHTML = dateTime.getDate() + ". " + (dateTime.getMonth() + 1) + ". " + dateTime.getFullYear();
    elementTime.innerHTML = hodiny + ":" + minuty + ":" + sekundy;
}