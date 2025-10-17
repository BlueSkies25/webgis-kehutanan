# webgis-leaflet (Dark Forest / Satellite)

This package includes a WebGIS PHP + MySQL project with:
- Leaflet frontend with basemap toggle (Dark Forest and Satellite)
- Sidebar with open/close (transparent circular button with arrow)
- "Locate me" button (shows marker + auto-zoom)
- Draw marker & polygon (Leaflet.draw) and save to MySQL (GeoJSON)
- Authentication (admin/user) - default users script included
- Designed to run on XAMPP (Windows)

Quick start (XAMPP)
1. Extract to: C:\xampp\htdocs\webgis-leaflet
2. Start Apache & MySQL (XAMPP)
3. Import init.sql via phpMyAdmin or CLI:
   "C:\xampp\mysql\bin\mysql.exe" -u root < "C:\xampp\htdocs\webgis-leaflet\init.sql"
4. (Optional) Create default users (admin/user):
   "C:\xampp\php\php.exe" create_default_users.php
5. Visit: http://localhost/webgis-leaflet/public/

Default users (if created):
- admin / 12345  (role: admin)
- user  / 12345  (role: user)

Notes:
- DB: host=127.0.0.1, name=webgis, user=root, pass=''
- Uses Esri WorldImagery (satellite) and Carto Dark for dark forest effect.
