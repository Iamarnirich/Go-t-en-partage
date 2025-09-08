document.addEventListener("DOMContentLoaded", function() {
  const form = document.querySelector('#formulaire2');
  const resultDiv = document.querySelector('#resultat_boisson');
  const boissonTypeSelect = document.querySelector('#boisson-type');

  // Chargement des options de boisson depuis l'API
  const xhr = new XMLHttpRequest();
  xhr.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/3?detail=0&key=fdb71dce-1a97-4831-aca8-e82cb278f51c', true);
  xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
          if (xhr.status === 200) {
              const reponse = JSON.parse(xhr.responseText);
              const données = reponse.data;
              données.forEach(boisson => {
                  let option = document.createElement('option');
                  option.value = boisson.slug;
                  option.textContent = boisson.name;
                  boissonTypeSelect.appendChild(option);
              });
              console.log("Options de boisson chargées avec succès");
          } else {
              console.error('Erreur lors du chargement des types de boisson:', xhr.statusText);
          }
      }
  };
  xhr.send();

  form.addEventListener('submit', function(event) {
      event.preventDefault();
      const boissonSlug = boissonTypeSelect.value;
      const quantite = document.querySelector('#quantite').value; // Quantité de boisson consommée par jour
      console.log("Boisson sélectionnée:", boissonSlug);

      // Calcul de l'empreinte carbone
      const xhr2 = new XMLHttpRequest();
      xhr2.open("GET", 'https://impactco2.fr/api/v1/thematiques/ecv/3?detail=0&key=fdb71dce-1a97-4831-aca8-e82cb278f51c', true);
      xhr2.onreadystatechange = function () {
          if (xhr2.readyState === 4) {
              if (xhr2.status === 200) {
                  const response = JSON.parse(xhr2.responseText);
                  const boisson = response.data.find(item => item.slug === boissonSlug);
                  if (boisson) {
                      const emissionsParLitre = boisson.ecv;
                      const emissionsTotales = emissionsParLitre * quantite * 5; // 5 jours de consommation
                      resultDiv.innerHTML = `Vos consommations produisent <strong>${emissionsTotales.toFixed(2)} kg de CO2e</strong> par semaine.`;
                      console.log("Émissions calculées:", emissionsTotales);
                  } else {
                      resultDiv.innerHTML = "Erreur : boisson non trouvée.";
                      console.error("Boisson non trouvée:", boissonSlug);
                  }
              } else {
                  console.error('Erreur lors du calcul des émissions:', xhr2.statusText);
                  resultDiv.innerHTML = "Erreur lors du calcul des émissions de CO2";
              }
          }
      };
      xhr2.send();
  });
});




    
  
    
