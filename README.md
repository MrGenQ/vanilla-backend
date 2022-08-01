# vanilla-backend

# Installation steps
<b>Clone repository</b></br>
<b>Copy .env.example file and rename it to .env</b></br>
<b>Create database named db2, if you want to use a different name you need to change .env file aswell. query: CREATE DATABASE db2</b></br>
<b>Run two mysql queries in file .UserTable and in .PokeTable</b></br>
<b>run few more queries in your database:</b></br>
  <p>CREATE USER 'db_user'@'localhost' IDENTIFIED BY 'api_password';</p></br>
  <p>GRANT ALL PRIVILEGES ON `db2` . * TO 'db_user'@'localhost';</p></br>
<b>Finally navigate to your backend project and run terminal command:</b></br>
<b>php -S 127.0.0.1:8000 -t public</b></br>
