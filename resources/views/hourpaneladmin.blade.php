<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="{{ asset('./assets/css/style-hourpanel.css') }}" media="screen">
    <script src="{{asset('/assets/js/jquery.min.js')}}"></script>
    <title>Hour Panel Admin</title>
</head>
<body style="background-color: gray">
    <div class="panel">
        <div class="hour-div">
            <div>
                @if(Session::get('success'))
                    <div style="color:rgb(60, 255, 0); text-align:center">
                        {{Session::get('success')}}
                    </div>
                @endif
                @if(Session::get('fail'))
                    <div style="color:rgb(255, 0, 0) text-align:center">
                        {{Session::get('fail')}}
                    </div>
                @endif
            </div>
            <table id="hour-table">
            </table>
        </div>
        <div class="fixed-bar">
            <table class="table-bar">
                <tr>
                    <th class="th-bar">
                        <a href="{{url('/main/adminpanel')}}">
                        <img class="icon-bar" src="{{ asset('./assets/icon/933159-time/svg/008-clipboard.svg') }}" >
                        <div class="voice-menu">Admin Panel</div></a>
                    </th>
                    <th class="th-bar active">
                        <a href="{{url('/main/calendaradmin')}}">
                        <img class="icon-bar" src="{{ asset('./assets/icon/933159-time/svg/011-calendar.svg')}}" >
                        <div class="voice-menu">Calendar</div></a>
                    </th>
                    <th class="th-bar">
                        <a href="{{url('/main/bookingtoday')}}">
                        <img class="icon-bar" src="{{ asset('./assets/icon/933159-time/svg/013-24-hours.svg')}}" >
                        <div class="voice-menu">BookingToday</div></a>
                    </th>
                    <th class="th-bar">
                        <a href="{{url('/main/logout')}}"><img class="icon-bar" src="{{ asset('./assets/icon/new/2214905-barber/svg/020-push-button.svg')}}" >
                        <div class="voice-menu">Logout</div></a>
                    </th>
                </tr>
            </table>
        </div>
    </div>

    <script>
        var array = [];
        var url;
        var bookings = {!! json_encode($bookings, JSON_HEX_TAG) !!};
        var times = {!! json_encode($time, JSON_HEX_TAG) !!};
        var barber = {!! json_encode($barber, JSON_HEX_TAG) !!};
        var barbers = {!! json_encode($barbers, JSON_HEX_TAG) !!};
        var servicechoice = {!! json_encode($service, JSON_HEX_TAG) !!};
        var services = {!! json_encode($services, JSON_HEX_TAG) !!};

        for(var l = 0; l < times.length; l++){
            if(servicechoice.id == times[l].Id)
                var timeservchoice = times[l].time;
        }

        for(var i = 0; i < 110; i++)
            array[i] = 0;
        for(var bb in bookings){
            if(bookings[bb].idE == barber.id)
            for(var j = 0; j < times.length; j++){
                if(bookings[bb].idS == times[j].Id){
                    var durata = times[j].time/5;//calcolo quanti slot di 5 min occupa questa prenotazione
                    for(var k = bookings[bb].index; k < (bookings[bb].index)+durata; k++)
                        array[k] = 1;//occupo con 1
                }
            }
        }

    var title = "";

    var tablehour = document.getElementById('hour-table');
    title = "<th colspan='5'>"+barber.name+"</th>";
    var barbern = document.createElement('tr');
    barbern.innerHTML = title;
    tablehour.appendChild(barbern);
    title = "";

    stampaOrari(timeservchoice,array);

	function stampaOrari(durata_servizio, arr)//funzione stampa orari liberi in base alla durata del servizio scelto
	{
        var tablehour = document.getElementById('hour-table');
        var c = 0;
        var td = "";

        var l = durata_servizio/5;//numero di caselle 0 affinchè possiamo dire se è libero(infatti dipende dalla durata)
        for(var i=0; i < 110-l; i++)//qui scorriamo l'array tutto, fino a j caselle prima della fine
        {
            var count=0;
            for (var k = i; k < i+l; k++)//qui scorriamo di l caselle dall'indice i
            {
                if (arr[k] == 0) count++;//e controlliamo uno ad uno se è libero o occupato.se libero conta
                else break;
            }
            if (count == l){
                url = '{{ route("hour.bookingadmin", ":hour") }}';
                var url = url.replace(':hour', i);
                c++;
                td += "<td><form action="+url+" method='get'><button class='hour-btn'>"+calcolaOrario(i)+"</button></form></td>"; i += l-1;
                if(c == 5){
                    var tr = document.createElement('tr');
                    tr.innerHTML = td;
                    tablehour.appendChild(tr);
                    td = "";
                    c = 0;
                }
            }//se abbiamo contato fino a l, vuol dire che abbiamo provato che quell'orario è libero e stampa in formato orario calcolato dalla funzione calcolaorario
        }
        var tr = document.createElement('tr');
        tr.innerHTML = td;
        tablehour.appendChild(tr);
        td = "";
        var html = "";
        c = 0;


        for(var bar in barbers){
            var url3 = '{{ route("barber.viewhour",  ":barberid") }}';
            url3 = url3.replace(':barberid', barbers[bar].id);
            html += "<th colspan='5'><form action="+url3+" method='get'><button type='submit' class='btn-select'>"+barbers[bar].name+"</button></form></th>";
            var barbername = document.createElement('tr');
            barbername.innerHTML = html;
            tablehour.appendChild(barbername);
            html = "";
        }

	}

    function calcolaOrario(tmp)//gli passiamo l'indice dell'orario trovato libero
	{
        var stringa;
        var orario= tmp * 5;//cosi troviamo i minuti di distacco dalle 09(o orario apertura) all'orario trovato libero
        var count = 0;
        while (orario >= 60)//fin quando ci sono 60 minuti da togliere
        {
            orario-=60;//togli 60 minuti
            count++;//e aggiungi un'ora
        }
        count+=9;//in count avremmo le ore di distacco dalle 8 all'orario trovato libero, infatti sommiamo 8 per risalire a l'ora esatta
        if(count >= 12)
            count += 2;
        console.log(count+":"+orario);
        if(orario == 0){
            if(count == 9)
                stringa= "0"+count+":"+orario+"0";
            else stringa= count+":"+orario+"0";
        }
        else {
            if(count == 9)
                stringa= "0"+count+":"+orario;
            else stringa= count+":"+orario;
        }//nella var orario ci resteranno i minuti che saranno per certo < di 60 e quindi concateniamo all'ora esatta trovata sopra. fine
        return stringa;
	}

    </script>
</body>
</html>
