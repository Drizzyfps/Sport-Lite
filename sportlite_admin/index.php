:root {
    --color-primary: #27ae60; 
    --color-secondary: #2ecc71; 
    --color-accent: #3498db; 
    --color-text-primary: #fff; 
    --color-text-secondary: #333; 
    --color-background: #f4f4f4; 
    --color-background-content: #f9f9f9; 
    --color-border: #d4d4d4; 
    --color-hover-primary: #25a357; 
    --color-hover-accent: #2980b9; 
}


body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--color-background);
    margin: 0;
    padding: 0;
    display: flex;
    min-height: 100vh;
    align-items: stretch; 
}


.sidebar {
    background-color: var(--color-primary);
    color: var(--color-text-primary);
    padding: 20px;
    width: 250px;
    border-right: 2px solid var(--color-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.sidebar h2 {
    color: var(--color-text-primary);
    margin-bottom: 30px;
    font-size: 2em;
    font-weight: bold;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    width: 100%;
}

.sidebar-menu li {
    margin-bottom: 10px;
}

.sidebar-menu li a {
    display: block;
    background-color: var(--color-secondary);
    color: var(--color-text-primary);
    padding: 12px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
    text-align: left;
}

.sidebar-menu li a:hover {
    background-color: var(--color-hover-primary);
}

.sidebar-submenu {
    list-style: none;
    padding-left: 20px;
    margin-top: 5px;
    display: none;
}

.sidebar-submenu li a {
    background-color: var(--color-hover-primary);
    font-size: 0.9em;
    border-radius: 5px;
    margin-bottom: 5px;
}

.sidebar-submenu li a:hover {
    background-color: #1e7e34; 
}


.content {
    flex-grow: 1;
    padding: 30px;
    background-color: var(--color-background-content);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start; 
    min-height: calc(100vh - 40px); 
}

.content h2 {
    color: var(--color-primary);
    margin-bottom: 30px;
    font-size: 2.5em;
    font-weight: bold;
}


.dashboard-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 30px;
    width: 80%;
    max-width: 960px;
}

.dashboard-button {
    background-color: var(--color-accent);
    color: var(--color-text-primary);
    padding: 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1em;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.2s ease-in-out;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.dashboard-button:hover {
    background-color: var(--color-hover-accent);
    transform: scale(1.03);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}


.content a {
    color: var(--color-accent);
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

.content a:hover {
    color: #2980b9;
    text-decoration: underline;
}
