import React, { useState } from "react";
import { Page, FormLayout, TextField, Button } from "@shopify/polaris";
import axios from "axios";
import { useNavigate } from "react-router-dom";

const Login = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const navigate = useNavigate();

    const handleSubmit = async () => {
        try {
            const response = await axios.post("/api/login", {
                email,
                password,
            });
            localStorage.setItem("token", response.data.token);
            axios.defaults.headers.common[
                "Authorization"
            ] = `Bearer ${response.data.token}`;
            navigate("/dashboard");
        } catch (err) {
            setError("Invalid credentials");
        }
    };

    return (
        <Page title="Login">
            <FormLayout>
                <TextField
                    label="Email"
                    value={email}
                    onChange={setEmail}
                    type="email"
                />
                <TextField
                    label="Password"
                    value={password}
                    onChange={setPassword}
                    type="password"
                />
                {error && <p style={{ color: "red" }}>{error}</p>}
                <Button primary onClick={handleSubmit}>
                    Login
                </Button>
            </FormLayout>
        </Page>
    );
};

export default Login;
