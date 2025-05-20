<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>VALIDADOR DE IDENTIFICACION</title>
</head>
<body>
  <div id="validation-result"></div>
  <input type="file" id="fileInputFront" accept=".png, .jpg, .jpeg">
  <input type="file" id="fileInputReverse" accept=".png, .jpg, .jpeg">
  <button id="uploadButtonFront" disabled>Subir Imagen Frontal</button>
  <button id="uploadButtonReverse" disabled>Subir Imagen Reverso</button>
  <button id="validateButton" disabled>Validar</button>
  <img id="frontImage" style="display: none; max-width: 300px; max-height: 300px;">
  <img id="reverseImage" style="display: none; max-width: 300px; max-height: 300px;">
  <script>
    const apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2NvdW50X2lkIjoiIiwiYWRkaXRpb25hbF9kYXRhIjoie30iLCJjbGllbnRfaWQiOiJUQ0kxYzc4NzM3OGQ2OTM5Nzk1NWQ2MGQ5YjI2NmU3ZDQzZCIsImV4cCI6MzI3NzQ4MjI0NSwiZ3JhbnQiOiIiLCJpYXQiOjE3MDA2ODIyNDUsImlzcyI6Imh0dHBzOi8vY29nbml0by1pZHAudXMtZWFzdC0xLmFtYXpvbmF3cy5jb20vdXMtZWFzdC0xX0Z4RjkzY1EwRiIsImp0aSI6IjBjMTQyZmJjLWQ5NGMtNDJhOC05N2FmLTI2ZGRiZDY0NjMzZiIsImtleV9uYW1lIjoianBpdHRvIiwia2V5X3R5cGUiOiJiYWNrZW5kIiwidXNlcm5hbWUiOiJhbHRhaXJ1bmxpbWl0ZWQtanBpdHRvIn0.C_VLlwHKUcUZwJfa7agBVoGz26Zhvyr_mV3jUF1Wrv8';
    const validationUrl = 'https://api.validations.truora.com/v1/validations';
    const imageUrlInputFront = document.getElementById('fileInputFront');
    const imageUrlInputReverse = document.getElementById('fileInputReverse');
    const uploadButtonFront = document.getElementById('uploadButtonFront');
    const uploadButtonReverse = document.getElementById('uploadButtonReverse');
    const validateButton = document.getElementById('validateButton');
    const validationResult = document.getElementById('validation-result');
    const frontImage = document.getElementById('frontImage');
    const reverseImage = document.getElementById('reverseImage');

    let validationId;
    let accountId;
    let frontUrl;
    let reverseUrl;

    const validationData = {
      type: 'document-validation',
      country: 'CO',
      document_type: 'national-id',
      user_authorized: true,
      account_id: 8989
    };

    function encodeFormData(data) {
      return Object.keys(data)
        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key]))
        .join('&');
    }

    // Listener para el botón "Subir Imagen Frontal"
    uploadButtonFront.addEventListener('click', () => {
      const file = imageUrlInputFront.files[0];

      if (!file) {
        alert('Por favor, selecciona un archivo frontal.');
        return;
      }

      const fileReader = new FileReader();
      fileReader.onload = function() {
        const binaryData = new Blob([new Uint8Array(this.result)]);

        // Utiliza 'frontUrl' para la carga frontal
        fetch(frontUrl, {
          method: 'PUT',
          body: binaryData
        })
          .then(response => {
            if (response.status === 200) {
              validationResult.textContent = 'Imagen frontal subida con éxito';
              // Habilita el botón de validación
              validateButton.removeAttribute('disabled');
            } else {
              console.error('Error al subir la imagen frontal:', response.statusText);
            }
          })
          .catch(err => {
            console.error('Error al subir la imagen frontal:', err);
          });
      };

      fileReader.readAsArrayBuffer(file);
    });

    // Listener para el botón "Subir Imagen Reverso"
    uploadButtonReverse.addEventListener('click', () => {
      const file = imageUrlInputReverse.files[0];

      if (!file) {
        alert('Por favor, selecciona un archivo de reverso.');
        return;
      }

      const fileReader = new FileReader();
      fileReader.onload = function() {
        const binaryData = new Blob([new Uint8Array(this.result)]);

        // Utiliza 'reverseUrl' para la carga de reverso
        fetch(reverseUrl, {
          method: 'PUT',
          body: binaryData
        })
          .then(response => {
            if (response.status === 200) {
              validationResult.textContent = 'Imagen de reverso subida con éxito';
              // Habilita el botón de validación
              validateButton.removeAttribute('disabled');
            } else {
              console.error('Error al subir la imagen de reverso:', response.statusText);
            }
          })
          .catch(err => {
            console.error('Error al subir la imagen de reverso:', err);
          });
      };

      fileReader.readAsArrayBuffer(file);
    });

    // Listener para el botón "Validar"
    validateButton.addEventListener('click', () => {
      // Realiza la validación aquí y muestra el mensaje de éxito
      validationResult.textContent = 'Imagen validada con éxito';
      // Aquí puedes utilizar 'validationId', 'accountId', 'frontUrl' y 'reverseUrl' según sea necesario.
    });

    // Listener para cargar la URL de validación al cargar la página
    fetch(validationUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Truora-API-Key': apiKey
      },
      body: encodeFormData(validationData)
    })
      .then(response => response.json())
      .then(data => {
        console.log('Respuesta del servidor:', data);

        if (data && data.validation_id && data.account_id && data.instructions && data.instructions.front_url && data.instructions.reverse_url) {
          validationId = data.validation_id;
          accountId = data.account_id;
          frontUrl = data.instructions.front_url;
          reverseUrl = data.instructions.reverse_url;

          // Habilita los botones para subir las imágenes una vez que tengamos las URLs
          uploadButtonFront.removeAttribute('disabled');
          uploadButtonReverse.removeAttribute('disabled');
        } else {
          validationResult.textContent = 'La respuesta no contiene la información necesaria.';
        }
      })
      .catch(err => {
        console.error(err);
        validationResult.textContent = 'Error en la solicitud de validación.';
      });

    // Función para obtener el ID de la validación
    function getValidationStatus() {
      // Asegúrate de tener el validationId obtenido previamente
      if (!validationId) {
        alert('Validation ID no encontrado. Por favor, primero inicia una validación.');
        return;
      } else {
        alert('ID_validacion: ' + validationId);
      }

      // URL para obtener el estado de la validación
      const validationStatusUrl = `https://api.validations.truora.com/v1/validations/${validationId}`;

      // Realiza una solicitud GET para obtener el estado e información de la validación
      fetch(validationStatusUrl, {
        method: 'GET',
        headers: {
          'Truora-API-Key': apiKey
        }
      })
        .then(response => response.json())
        .then(data => {
          console.log('Estado de la validación:', data);

          if (data.validation_status) {
            // Aquí puedes manejar el estado de la validación (success, failure, pending) y la información adicional según tus necesidades.
            validationResult.textContent = `Estado de la validación: ${data.validation_status}`;
          } else {
            validationResult.textContent = 'No se pudo obtener el estado de la validación.';
          }
        })
        .catch(err => {
          console.error(err);
          validationResult.textContent = 'Error al obtener el estado de la validación.';
        });
    }

    // Agrega un listener al botón "Obtener Estado de Validación"
    const getStatusButton = document.createElement('button');
    getStatusButton.textContent = 'Obtener Estado de Validación';
    getStatusButton.addEventListener('click', getValidationStatus);
    document.body.appendChild(getStatusButton);
  </script>
</body>
</html>
