import mysql from "mysql2";

export const db = mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "inmobiliaria"
});

db.connect((err) => {
    if (err) {
        console.error("Error de conexión:", err);
        return;
    }
    console.log("Conectado a la base de datos");
});
