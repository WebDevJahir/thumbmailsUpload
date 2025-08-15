import { createRoot } from "react-dom/client";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import { AppProvider } from "@shopify/polaris";
import enTranslations from "@shopify/polaris/locales/en.json";
import Dashboard from "./components/Dashboard";
import Login from "./components/Login";
import "./bootstrap";
import "@shopify/polaris/build/esm/styles.css";

const App = () => (
    <AppProvider i18n={enTranslations}>
        <Router>
            <Routes>
                <Route path="/login" element={<Login />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/" element={<Login />} />
            </Routes>
        </Router>
    </AppProvider>
);

createRoot(document.getElementById("root")).render(<App />);
