# Lagorii Kids eCommerce Website

## Overview

This is a children's clothing eCommerce website built with vanilla JavaScript, HTML, CSS, and Bootstrap. The site features a product catalog with categories for girls, boys, and infants, along with shopping cart functionality and user authentication. The architecture follows a client-side approach with local storage for data persistence and appears to be designed for integration with a PHP backend API.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Technology Stack**: Vanilla JavaScript, HTML5, CSS3, Bootstrap framework
- **Design Pattern**: Component-based architecture with separate JavaScript modules
- **Styling**: Custom CSS with Bootstrap components for responsive design
- **Data Storage**: Browser localStorage for cart and user session management

### Backend Architecture
- **API Integration**: Designed to work with PHP backend (referenced in auth.js)
- **Data Format**: JSON-based data structures for products and categories
- **Authentication**: Client-side session management with server-side validation

## Key Components

### 1. Product Management System
- **Product Catalog**: Static JSON files containing product and category data
- **Categories**: Hierarchical structure (girls/boys/infants with subcategories)
- **Product Features**: Images, pricing, sizes, colors, stock status, and tags

### 2. Shopping Cart System (`assets/js/cart.js`)
- **Local Storage**: Cart persistence across browser sessions
- **Item Management**: Add, update, remove items with options (size, color)
- **Validation**: Quantity limits and product availability checks
- **Real-time Updates**: Dynamic cart count and display updates

### 3. Authentication System (`assets/js/auth.js`)
- **User Management**: Login/logout functionality with local session storage
- **API Integration**: Communicates with PHP backend for authentication
- **Session Persistence**: User data stored in localStorage
- **UI Updates**: Dynamic authentication state management

### 4. Main Application (`assets/js/main.js`)
- **App Initialization**: Coordinates all components on page load
- **Navigation**: Smooth scrolling and responsive menu functionality
- **Search**: Product search functionality
- **UI Enhancements**: Scroll effects and interactive elements

### 5. Styling System (`assets/css/style.css`)
- **Responsive Design**: Mobile-first approach with Bootstrap integration
- **Custom Components**: Top banner with marquee animation
- **Visual Effects**: Gradients, shadows, and smooth transitions
- **Brand Identity**: Consistent color scheme and typography

## Data Flow

### Product Display Flow
1. Static JSON files (`data/products.json`, `data/categories.json`) contain product data
2. JavaScript modules load and parse JSON data
3. Products are filtered and displayed based on categories and search criteria
4. Product cards are dynamically generated with interactive elements

### Shopping Cart Flow
1. User selects products with options (size, color, quantity)
2. Cart items are validated and stored in localStorage
3. Cart state is synchronized across all pages
4. Cart data is prepared for checkout process

### Authentication Flow
1. User submits login credentials through frontend form
2. Data is sent to PHP backend API (`../api/auth.php`)
3. Successful authentication returns user data
4. User session is stored locally and UI is updated accordingly

## External Dependencies

### Frontend Libraries
- **Bootstrap**: UI framework for responsive design and components
- **Inter Font**: Primary typography from Google Fonts or similar service
- **Pixabay Images**: Product and category images hosted externally

### Backend Integration
- **PHP API**: Authentication and likely other backend services
- **Image Hosting**: External image URLs (Pixabay) for product media

## Deployment Strategy

### Current Setup
- **Static Hosting**: Frontend files can be served from any web server
- **Separate Backend**: PHP API likely hosted on separate server/domain
- **Client-Side Data**: Products and categories loaded from static JSON files

### Scalability Considerations
- **Database Migration**: JSON files should be moved to proper database system
- **API Expansion**: Additional endpoints needed for product management, orders
- **Image Management**: Consider CDN or dedicated media storage solution
- **Security**: Implement proper authentication tokens and CSRF protection

### Recommended Improvements
- **Database Integration**: Replace JSON files with database (PostgreSQL recommended)
- **API Framework**: Implement RESTful API for all data operations
- **Build Process**: Add bundling and optimization for production
- **Error Handling**: Implement comprehensive error handling and user feedback
- **Testing**: Add unit and integration tests for JavaScript modules