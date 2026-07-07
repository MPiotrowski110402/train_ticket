<div class="wagon">


<div class="wagon-header">

Wagon 12
<br>
Klasa 2

</div>



<div class="seats">


@for($i=1;$i<=40;$i++)

<button class="seat
@if($i%5==0)
occupied
@endif
">

{{ $i }}

</button>


@endfor


</div>


</div>