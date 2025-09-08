/* document.querySelector('#myForm').addEventListener('submit', function(event) {
    event.preventDefault();  */
    
    let TableauRepas = [];
    let TableauBoissons = [];
    let TableauTransport = [];
    let TableauNumerique = [];
    const sectionGenerer = document.querySelector('#sectionGenerer');

    // this.reset();


    const btnRepas = document.querySelector('#btnRepas');
    btnRepas.onclick = function(event){
        event.preventDefault();
        const carbone = document.querySelector('#carbone').value;
        const xhr1 = new XMLHttpRequest();
        xhr1.open("GET", "https://impactco2.fr/api/v1/thematiques/ecv/2?detail=0");
        xhr1.onreadystatechange = function () {
            if (xhr1.readyState === 4 && xhr1.status === 200) {
                const data = JSON.parse(xhr1.responseText);
                for (const elm of data.data){
                    const theme = {
                        Nom: elm.name,
                        Equiv: parseInt(carbone / elm.ecv)
                    };
                TableauRepas.push(theme);
                }
                btnRepas.style.backgroundColor = "black";
                btnTransport.style.backgroundColor = "rgb(65, 177, 0)";
                btnNumerique.style.backgroundColor = "rgb(65, 177, 0)";
                btnBoissons.style.backgroundColor = "rgb(65, 177, 0)";

                sectionGenerer.innerHTML = "";   
                for (const elm of TableauRepas){
                    sectionGenerer.innerHTML += '<div style="border: 2px solid black; width: 25%; display: inline-block; margin: 3%; border-radius: 10px;"> <h2>' + elm["Equiv"] + '</h2> <p style=" font-weight: bold; margin: 5px auto; font-size: medium;">' + elm["Nom"] + '</p> </div>';
                }
                TableauRepas = [];
            }
        }
        xhr1.send();

    }


    const btnNumerique = document.querySelector('#btnNumerique');
    btnNumerique.onclick= function(event){
        event.preventDefault();
        const carbone = document.querySelector('#carbone').value;
        const xhr2 = new XMLHttpRequest();
            xhr2.open("GET", "https://impactco2.fr/api/v1/thematiques/ecv/1?detail=1");
            xhr2.onreadystatechange = function () {
            if (xhr2.readyState === 4 && xhr2.status === 200) {
                const data = JSON.parse(xhr2.responseText);
                for (const elm of data.data){
                    const theme = {
                        Nom: elm.name,
                        Equiv: parseInt(carbone / elm.ecv)
                        
                    };
                    TableauNumerique.push(theme);
                }
                btnNumerique.style.backgroundColor = "black";
                btnTransport.style.backgroundColor = "rgb(65, 177, 0)";
                btnRepas.style.backgroundColor = "rgb(65, 177, 0)";
                btnBoissons.style.backgroundColor = "rgb(65, 177, 0)";

                sectionGenerer.innerHTML = "";
                for (const elm of TableauNumerique){
                    sectionGenerer.innerHTML += '<div style="border: 2px solid black; width: 25%; display: inline-block; margin: 3%; border-radius: 10px;"> <h2>' + elm["Equiv"] + '</h2> <p style=" font-weight: bold; margin: 5px auto; font-size: medium;">' + elm["Nom"] + '</p> </div>';
                }
                TableauNumerique = [];
            }
        }
        xhr2.send();

    }


    const btnBoissons = document.querySelector('#btnBoissons');
    btnBoissons.onclick = function(event){
        event.preventDefault();
        const carbone = document.querySelector('#carbone').value;
        const xhr3 = new XMLHttpRequest();
            xhr3.open("GET", "https://impactco2.fr/api/v1/thematiques/ecv/3?detail=1");
            xhr3.onreadystatechange = function () {
            if (xhr3.readyState === 4 && xhr3.status === 200) {
                const data = JSON.parse(xhr3.responseText);
                for (const elm of data.data){
                    const theme = {
                        Nom: elm.name,
                        Equiv: parseInt(carbone / elm.ecv)
                    }
                    TableauBoissons.push(theme);
                }
                btnBoissons.style.backgroundColor = "black";
                btnTransport.style.backgroundColor = "rgb(65, 177, 0)";
                btnRepas.style.backgroundColor = "rgb(65, 177, 0)";
                btnNumerique.style.backgroundColor = "rgb(65, 177, 0)";
                
                sectionGenerer.innerHTML = "";
                for (const elm of TableauBoissons){
                    sectionGenerer.innerHTML += '<div style="border: 2px solid black; width: 25%; display: inline-block; margin: 3%; border-radius: 10px;"> <h2>' + elm["Equiv"] + '</h2> <p style=" font-weight: bold; margin: 5px auto; font-size: medium;">' + elm["Nom"] + '</p> </div>';
                }
                TableauBoissons = [];
            }
        }
        xhr3.send();
    }


    const btnTransport = document.querySelector('#btnTransport');
    btnTransport.onclick = function(event){
        event.preventDefault();
        const carbone = document.querySelector('#carbone').value;
        const xhr4 = new XMLHttpRequest();
            xhr4.open("GET", "https://impactco2.fr/api/v1/thematiques/ecv/4?detail=1");
            xhr4.onreadystatechange = function () {
            if (xhr4.readyState === 4 && xhr4.status === 200) {
                const data = JSON.parse(xhr4.responseText);
                let TableauTheme = [];
                for (const elm of data.data){
                    const theme = {
                        Nom: elm.name,
                        Equiv: parseInt(carbone / elm.ecv)
                    }
                    TableauTransport.push(theme);
                }
                btnTransport.style.backgroundColor = "black";
                btnRepas.style.backgroundColor = "rgb(65, 177, 0)";
                btnNumerique.style.backgroundColor = "rgb(65, 177, 0)";
                btnBoissons.style.backgroundColor = "rgb(65, 177, 0)";

                sectionGenerer.innerHTML = "";
                for (const elm of TableauTransport){
                    sectionGenerer.innerHTML += '<div style="border: 2px solid black; width: 25%; display: inline-block; margin: 3%; border-radius: 10px"> <h2>' + elm["Equiv"] + '</h2> <p style=" font-weight: bold; margin: 5px auto; font-size: medium;">' + elm["Nom"] + '</p> </div>';
                }
                TableauTransport = [];
            }
        }
        xhr4.send();
    }