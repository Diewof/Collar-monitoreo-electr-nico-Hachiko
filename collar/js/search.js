/**
 * Script para manejar la funcionalidad de búsqueda en tiempo real
 */

document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.getElementById('search-results');
    
    // Estructura de datos para los elementos de navegación
    const navItems = [
        { 
            text: "Nueva mascota", 
            url: "nueva_mascota.php",
            category: "Mi Mascota"
        },
        { 
            text: "Estado emocional", 
            url: "estado_emocional.php",
            category: "Mi Mascota"
        },
        { 
            text: "Perfil de mascota", 
            url: "perfil_mascota.php",
            category: "Mi Mascota"
        },
        { 
            text: "Estado emocional actual", 
            url: "estado_actual.php",
            category: "Análisis"
        },
        { 
            text: "Soluciones etológicas", 
            url: "soluciones.php",
            category: "Análisis"
        },
        { 
            text: "Programar paseo", 
            url: "programar_paseo.php",
            category: "Análisis"
        },
        { 
            text: "Reporte emocional mensual", 
            url: "reporte_mensual.php",
            category: "Informes"
        },
        { 
            text: "Reporte anual", 
            url: "reporte_anual.php",
            category: "Informes"
        },
        { 
            text: "Exportar Datos", 
            url: "exportar_datos.php",
            category: "Informes"
        }
    ];

    // Función para filtrar los resultados de búsqueda
    function filterItems(query) {
        if (!query) return [];
        
        query = query.toLowerCase();
        return navItems.filter(item => 
            item.text.toLowerCase().includes(query) || 
            item.category.toLowerCase().includes(query)
        );
    }

    // Función para mostrar los resultados de búsqueda
    function displayResults(results) {
        // Limpiar resultados anteriores
        searchResults.innerHTML = '';
        
        // Ocultar el contenedor si no hay resultados
        if (results.length === 0) {
            searchResults.classList.remove('active');
            return;
        }
        
        // Mostrar el contenedor de resultados
        searchResults.classList.add('active');
        
        // Agrupar resultados por categoría
        const categories = {};
        results.forEach(item => {
            if (!categories[item.category]) {
                categories[item.category] = [];
            }
            categories[item.category].push(item);
        });
        
        // Crear elementos de resultados agrupados por categoría
        for (const category in categories) {
            const categoryHeader = document.createElement('div');
            categoryHeader.className = 'search-category';
            categoryHeader.textContent = category;
            searchResults.appendChild(categoryHeader);
            
            categories[category].forEach(item => {
                const resultItem = document.createElement('a');
                resultItem.className = 'search-result-item';
                resultItem.href = item.url;
                resultItem.textContent = item.text;
                
                // Añadir evento de clic para navegación
                resultItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.location.href = item.url;
                    searchResults.classList.remove('active');
                    searchInput.value = '';
                });
                
                searchResults.appendChild(resultItem);
            });
        }
    }

    // Manejar eventos de entrada en el campo de búsqueda
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        const results = filterItems(query);
        displayResults(results);
    });
    
    // Ocultar resultados cuando el input pierde el foco
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('active');
        }
    });
    
    // Mostrar resultados al enfocar el campo de búsqueda si hay texto
    searchInput.addEventListener('focus', () => {
        const query = searchInput.value.trim();
        if (query) {
            const results = filterItems(query);
            displayResults(results);
        }
    });
});