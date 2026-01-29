# Inasistig - Ecosistema Digital EIE

Sistema integral de gestión educativa diseñado para potenciar la excelencia pedagógica a través de herramientas inteligentes y un diseño centrado en el docente.

## 🎯 Propósito

**Inasistig** es una aplicación web moderna que facilita la gestión académica en contextos educativos, permitiendo a los docentes controlar asistencias, registrar incidencias y documentar el proceso pedagógico de manera eficiente.

## ✨ Características Principales

### 📊 Registro Diario
- Control preciso de inasistencias y novedades diarias del aula
- Gestión operativa de asistencia con interfaz intuitiva

### 📝 Anotador de Clase  
- Registro dinámico de incidencias y avances pedagógicos por sesión
- Seguimiento ágil del progreso estudiantil

### 📖 Diario de Campo
- Espacio para reflexión profunda y documentación pedagógica
- Herramienta estratégica para la mejora continua docente

## 🛠️ Stack Tecnológico

- **Frontend**: Svelte 5 + TypeScript
- **Build Tool**: Vite 7.2.4
- **Estilos**: TailwindCSS 4.1.18
- **Transiciones**: Svelte transitions
- **Alertas**: SweetAlert2
- **Despliegue**: gh-pages

## 🎨 Diseño y Experiencia

- **Interfaz Moderna**: Diseño futurista con efectos glassmorphism
- **Modo Tema**: Soporte para temas claro, dim y oscuro
- **Transiciones Fluidas**: Animaciones suaves y microinteracciones
- **Responsive**: Adaptación completa a dispositivos móviles y escritorio
- **Accesibilidad**: Navegación intuitiva y controles accesibles

## 🚀 Instalación y Uso

### Prerrequisitos
- Node.js 18+ 
- npm o yarn

### Instalación
```bash
# Clonar el repositorio
git clone <repository-url>
cd inasistig

# Instalar dependencias
npm install

# Iniciar servidor de desarrollo
npm run dev
```

### Scripts Disponibles
```bash
npm run dev      # Servidor de desarrollo
npm run build    # Construcción para producción
npm run preview  # Previsualizar build
npm run check    # Verificación de tipos y Svelte
npm run deploy   # Despliegue a GitHub Pages
```

## 📁 Estructura del Proyecto

```
src/
├── components/          # Componentes principales
│   ├── Dashboard.svelte     # Vista principal con navegación
│   ├── InasistenciaForm.svelte # Formulario de registro diario
│   ├── Anotador.svelte      # Módulo de anotaciones
│   ├── Diario.svelte        # Diario de campo
│   └── Loader.svelte        # Componente de carga
├── lib/                # Utilidades y stores
│   ├── themeStore.ts       # Gestión de temas
│   └── Counter.svelte      # Componente utilitario
├── assets/             # Recursos estáticos
├── constants.ts        # Constantes de la aplicación
├── App.svelte          # Componente raíz
└── main.ts            # Punto de entrada
```

## 🎯 Funcionalidades por Módulo

### Dashboard
- Navegación centralizada entre módulos
- Selector de temas con animaciones
- Vista general del sistema

### Registro Diario
- Formulario optimizado para control de asistencia
- Validación en tiempo real
- Exportación de datos

### Anotador de Clase
- Registro rápido de incidencias
- Categorización de eventos
- Búsqueda y filtrado

### Diario de Campo
- Editor de texto enriquecido
- Organización por fechas
- Reflexiones pedagógicas

## 🔧 Configuración

### Variables de Entorno
El proyecto utiliza configuración por defecto. Para personalización:

```typescript
// src/constants.ts
export const APP_CONFIG = {
  version: "2.0.4",
  theme: "light" // light | dim | dark
};
```

### Temas Personalizados
Los temas se gestionan a través de CSS variables en `src/lib/themeStore.ts`.

## 📦 Build y Despliegue

### Construcción para Producción
```bash
npm run build
```

### Despliegue Automatizado
```bash
npm run deploy
```
El proyecto se configura automáticamente para despliegue en GitHub Pages.

## 🤝 Contribución

1. Fork del proyecto
2. Crear feature branch (`git checkout -b feature/amazing-feature`)
3. Commit cambios (`git commit -m 'Add amazing feature'`)
4. Push al branch (`git push origin feature/amazing-feature`)
5. Abrir Pull Request

## 📄 Licencia

Este proyecto es parte del Ecosistema Digital EIE - 2026

## 🆘 Soporte

Para soporte técnico o sugerencias:
- Crear un issue en el repositorio
- Contactar al equipo de desarrollo EIE

---

**Versión**: 2.0.4 PLATINUM  
**Última Actualización**: Enero 2026  
**Desarrollado por**: EIE Digital Team