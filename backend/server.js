import express from "express";

const app = express();
const PORT = 3000;

app.use(express.json());

app.get("/", (req, res) => {
    res.send("API funcionando 🚀");
});

app.listen(PORT, () => {
    console.log(`Servidor en http://localhost:${PORT}`);
});
