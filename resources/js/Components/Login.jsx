import React, { useState } from "react";
import {
    Page,
    FormLayout,
    TextField,
    Button,
    Card,
    Layout,
    Banner,
} from "@shopify/polaris";
import axios from "axios";
import { useNavigate } from "react-router-dom";

const Login = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState(null); // Null for no error initially
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
            setError("Invalid credentials. Please try again.");
        }
    };

    return (
        <Page title="Login" narrowWidth>
            <Layout>
                <Layout.Section>
                    <Card sectioned>
                        <FormLayout>
                            <TextField
                                label="Email"
                                value={email}
                                onChange={setEmail}
                                type="email"
                                autoComplete="email"
                                placeholder="Enter your email"
                                required
                            />
                            <TextField
                                label="Password"
                                value={password}
                                onChange={setPassword}
                                type="password"
                                autoComplete="current-password"
                                placeholder="Enter your password"
                                required
                            />
                            {error && (
                                <Banner
                                    status="critical"
                                    onDismiss={() => setError(null)}
                                >
                                    {error}
                                </Banner>
                            )}
                            <Button primary fullWidth onClick={handleSubmit}>
                                Login
                            </Button>
                        </FormLayout>
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
};

export default Login;
