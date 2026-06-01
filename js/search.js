// Función para obtener el token de acceso de manera segura desde el backend PHP
async function getAccessToken() {
    try {
        const response = await fetch('../php/get_spotify_token.php');

        if (!response.ok) {
            throw new Error(`Error al obtener el token: ${response.statusText}`);
        }

        const data = await response.json();
        return data.access_token;
    } catch (error) {
        console.error('Error al obtener el token de acceso:', error);
        return null;
    }
}

// Función para buscar un artista, sus álbumes, canciones y sencillos
async function searchArtist(artistName) {
    const resultsContainer = document.getElementById('results-container');
    resultsContainer.innerHTML = '<p>Buscando...</p>';

    try {
        const accessToken = await getAccessToken();
        if (!accessToken) {
            resultsContainer.innerHTML = '<p>Error al obtener el token de acceso.</p>';
            return;
        }

        // Buscar el artista
        const artistResponse = await fetch(`https://api.spotify.com/v1/search?q=${encodeURIComponent(artistName)}&type=artist&limit=1`, {
            headers: { Authorization: `Bearer ${accessToken}` },
        });

        if (!artistResponse.ok) throw new Error(`Error al buscar artista: ${artistResponse.statusText}`);

        const artistData = await artistResponse.json();

        if (!artistData.artists.items.length) {
            resultsContainer.innerHTML = '<p>No se encontró ningún artista con ese nombre.</p>';
            return;
        }

        const artistId = artistData.artists.items[0].id;

        // Obtener los álbumes del artista (incluyendo sencillos)
        const albumsResponse = await fetch(`https://api.spotify.com/v1/artists/${artistId}/albums?include_groups=album,single&limit=10`, {
            headers: { Authorization: `Bearer ${accessToken}` },
        });

        if (!albumsResponse.ok) throw new Error(`Error al obtener álbumes: ${albumsResponse.statusText}`);

        const albumsData = await albumsResponse.json();
        resultsContainer.innerHTML = ''; // Limpiar resultados previos

        if (albumsData.items.length === 0) {
            resultsContainer.innerHTML = '<p>No se encontraron álbumes o sencillos para este artista.</p>';
            return;
        }

        // Mostrar álbumes, canciones y reproductor
        for (const album of albumsData.items) {
            const albumElement = document.createElement('div');
            albumElement.classList.add('album');
            albumElement.innerHTML = `
                <img src="${album.images[0]?.url}" alt="${album.name}">
                <h3>${album.name}</h3>
                <div class="tracks"><p>Cargando canciones...</p></div>
                <iframe src="https://open.spotify.com/embed/album/${album.id}" width="100%" height="80" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
            `;
            resultsContainer.appendChild(albumElement);

            // Obtener canciones del álbum
            const tracksResponse = await fetch(`https://api.spotify.com/v1/albums/${album.id}/tracks`, {
                headers: { Authorization: `Bearer ${accessToken}` },
            });

            if (!tracksResponse.ok) throw new Error(`Error al obtener canciones del álbum: ${tracksResponse.statusText}`);

            const tracksData = await tracksResponse.json();
            const tracksContainer = albumElement.querySelector('.tracks');
            tracksContainer.innerHTML = tracksData.items.map(
                track => `<p><a href="https://open.spotify.com/track/${track.id}" target="_blank">${track.name}</a></p>`
            ).join('');
        }
    } catch (error) {
        console.error('Error detallado:', error);
        resultsContainer.innerHTML = '<p>Error al buscar artista. Intenta de nuevo.</p>';
    }
}

// Escuchar el botón de búsqueda
document.getElementById('search-btn').addEventListener('click', () => {
    const artistName = document.getElementById('artist-search').value.trim();
    if (!artistName) {
        document.getElementById('results-container').innerHTML = '<p>Por favor, ingresa el nombre de un artista.</p>';
        return;
    }
    searchArtist(artistName);
});
