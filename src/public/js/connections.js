document.addEventListener(
"DOMContentLoaded",
()=>{


/*
|--------------------------------------------------------------------------
| OPEN CONNECTION DETAILS
|--------------------------------------------------------------------------
*/


const detailButtons =
document.querySelectorAll(
".details-btn"
);



detailButtons.forEach(
button=>{


button.addEventListener(
"click",
()=>{


const card =
button.closest(
".connection-card"
);



card.classList.toggle(
"open"
);



if(card.classList.contains("open")){


button.innerHTML =
"▲ Ukryj szczegóły połączenia";


}else{


button.innerHTML =
"▼ Pokaż szczegóły połączenia";


}



});


});




/*
|--------------------------------------------------------------------------
| SEAT SELECT
|--------------------------------------------------------------------------
*/


const seats =
document.querySelectorAll(
".seat:not(.occupied)"
);



seats.forEach(
seat=>{


seat.addEventListener(
"click",
()=>{


seat.classList.toggle(
"selected"
);



updatePrice();


});


});




function updatePrice(){


const selected =
document.querySelectorAll(
".seat.selected"
);



console.log(
"Wybrane miejsca:",
selected.length
);


}





/*
|--------------------------------------------------------------------------
| SWAP CITIES
|--------------------------------------------------------------------------
*/


const swap =
document.querySelector(
".swap-button"
);



if(swap){


swap.addEventListener(
"click",
()=>{


const inputs =
document.querySelectorAll(
".route-input input"
);



let temp =
inputs[0].value;



inputs[0].value =
inputs[1].value;



inputs[1].value =
temp;


});


}



});