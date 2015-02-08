set_dateTime(); // inicializuje čas
setInterval(set_dateTime, 1000); // změní čas každou sekundu

function set_dateTime() // nastaví čas a datum do příslušných polí
{
    var elementDate = document.getElementById("date")
    var elementTime = document.getElementById("time");
    var dateTime    = new Date();

    var hodiny = dateTime.getHours() < 10 ? "0"+dateTime.getHours() : dateTime.getHours();
    var minuty = dateTime.getMinutes() < 10 ? "0"+dateTime.getMinutes() : dateTime.getMinutes();
    var sekundy = dateTime.getSeconds() < 10 ? "0"+dateTime.getSeconds() : dateTime.getSeconds();

    elementDate.innerHTML = dateTime.getDate() + ". " + (dateTime.getMonth() + 1) + ". " + dateTime.getFullYear(); // vloží datum
    elementTime.innerHTML = hodiny + ":" + minuty + ":" + sekundy; // vloží čas
}