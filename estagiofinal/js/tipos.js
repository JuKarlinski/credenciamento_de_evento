function myFunction() {

    var input = document.getElementById("myInput");

    var filtro = input.value.toUpperCase();

    var tabela = document.getElementById("myTable");

    var linhas = tabela.getElementsByTagName("tr");


    for (var i = 1; i < linhas.length; i++) {

        var colunas = linhas[i].getElementsByTagName("td");

        var encontrou = false;


        for (var j = 0; j < colunas.length; j++) {

            var texto = colunas[j].textContent || colunas[j].innerText;


            if (texto.toUpperCase().indexOf(filtro) > -1) {

                encontrou = true;
                break;

            }

        }


        if (encontrou) {

            linhas[i].style.display = "";

        } else {

            linhas[i].style.display = "none";

        }

    }

}