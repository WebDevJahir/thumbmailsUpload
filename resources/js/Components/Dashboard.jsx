import React, { useState, useEffect } from "react";
import {
    Page,
    Card,
    FormLayout,
    TextField,
    Button,
    DataTable,
    Select,
    Banner,
    List,
    Layout,
    Spinner,
} from "@shopify/polaris";
import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

const Dashboard = () => {
    const [urls, setUrls] = useState("");
    const [requests, setRequests] = useState([]);
    const [statusFilter, setStatusFilter] = useState("");
    const [notifications, setNotifications] = useState([]);
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null); // For validation errors

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            window.location.href = "/login";
            return;
        }
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        fetchInitialData();
    }, []);

    useEffect(() => {
        if (!user?.id) return;

        const channel = window.Echo.private(`user.${user.id}`)
            .listen("ImageProcessed", (e) => {
                updateTable(e.image);
            })
            .notification((notification) => {
                fetchNotifications();
            });

        return () => {
            channel.stopListening();
        };
    }, [user?.id]);

    const fetchInitialData = async () => {
        setLoading(true);
        try {
            await Promise.all([fetchData(), fetchNotifications(), fetchUser()]);
        } finally {
            setLoading(false);
        }
    };

    const fetchUser = async () => {
        try {
            const response = await axios.get("/api/user");
            setUser(response.data);
        } catch (err) {
            console.error("Failed to fetch user");
        }
    };

    const fetchData = async () => {
        try {
            const response = await axios.get(
                `/api/bulk-requests?status=${statusFilter}`
            );
            setRequests(response.data);
        } catch (err) {
            console.error("Failed to fetch requests", err.response?.data);
        }
    };

    const fetchNotifications = async () => {
        try {
            const response = await axios.get("/api/notifications");
            setNotifications(response.data);
        } catch (err) {
            console.error("Failed to fetch notifications");
        }
    };

    const handleSubmit = async () => {
        setLoading(true);
        setError(null); // Clear previous errors
        try {
            const urlsArray = urls
                .split("\n")
                .map((url) => url.trim())
                .filter((url) => url);
            if (urlsArray.length > 50 && user?.tier === "free") {
                setError(
                    "Free users can upload a maximum of 50 images at a time."
                );
                return;
            }
            if (urlsArray.length === 0) {
                setError("Please provide at least one URL.");
                return;
            }
            const uniqueUrls = new Set(urlsArray);
            if (uniqueUrls.size !== urlsArray.length) {
                setError("Duplicate URLs are not allowed.");
                return;
            }
            if (!urlsArray.every((url) => /^https?:\/\//.test(url))) {
                setError("All URLs must start with http:// or https://");
                return;
            }

            await axios.post("/api/bulk-requests", { urls });
            setUrls("");
            await fetchData();
        } catch (err) {
            setError(
                err.response?.data?.message ||
                    "An error occurred during submission."
            );
        } finally {
            setLoading(false);
        }
    };

    const updateTable = (updatedImage) => {
        setRequests((prevRequests) => {
            const updated = prevRequests.map((req) => ({
                ...req,
                images: req.images.map((img) =>
                    img.id === updatedImage.id
                        ? {
                              ...img,
                              status: updatedImage.status,
                              processed_at: updatedImage.processed_at,
                          }
                        : img
                ),
            }));
            return updated;
        });
    };

    const markAsRead = async (id) => {
        try {
            await axios.post(`/api/notifications/${id}/mark-as-read`);
            fetchNotifications();
        } catch (err) {
            console.error("Failed to mark as read");
        }
    };

    const handleLogout = () => {
        localStorage.removeItem("token");
        window.location.href = "/login";
    };

    const rows = requests
        .flatMap((req) =>
            req.images
                ? req.images.map((img) => [
                      img.url || "N/A",
                      img.status
                          ? img.status.charAt(0).toUpperCase() +
                            img.status.slice(1)
                          : "Unknown",
                      img.processed_at || img.created_at || "N/A",
                  ])
                : []
        )
        .filter(
            (row) =>
                row.length === 3 &&
                (!statusFilter || row[1].toLowerCase() === statusFilter)
        );

    return (
        <Page title="Dashboard" narrowWidth>
            <Layout>
                <Layout.Section>
                    <Card sectioned>
                        <div
                            style={{
                                display: "flex",
                                justifyContent: "flex-end",
                            }}
                        >
                            <Button onClick={handleLogout} destructive>
                                Logout
                            </Button>
                        </div>
                    </Card>
                    {loading && (
                        <div style={{ textAlign: "center", padding: "20px" }}>
                            <Spinner accessibilityLabel="Loading dashboard data" />
                        </div>
                    )}
                    {notifications.length > 0 && (
                        <Card sectioned>
                            <Banner title="Notifications" status="info">
                                <List>
                                    {notifications.map((notif) => (
                                        <List.Item key={notif.id}>
                                            <div
                                                style={{
                                                    display: "flex",
                                                    justifyContent:
                                                        "space-between",
                                                }}
                                            >
                                                <span>
                                                    {notif.data.message}
                                                </span>
                                                <Button
                                                    plain
                                                    onClick={() =>
                                                        markAsRead(notif.id)
                                                    }
                                                >
                                                    Mark as Read
                                                </Button>
                                            </div>
                                        </List.Item>
                                    ))}
                                </List>
                            </Banner>
                        </Card>
                    )}
                    <Card sectioned>
                        <FormLayout>
                            <TextField
                                label="Image URLs (one per line)"
                                multiline={4}
                                value={urls}
                                onChange={setUrls}
                                placeholder="Enter URLs separated by new lines"
                                helpText="e.g., https://example.com/image1.jpg"
                            />
                            <Button
                                primary
                                onClick={handleSubmit}
                                disabled={loading}
                                loading={loading}
                            >
                                Submit
                            </Button>
                            {error && (
                                <Banner
                                    status="critical"
                                    onDismiss={() => setError(null)}
                                >
                                    {error}
                                </Banner>
                            )}
                        </FormLayout>
                    </Card>
                    <Card sectioned>
                        <Select
                            label="Filter by Status"
                            options={[
                                { label: "All", value: "" },
                                { label: "Processed", value: "processed" },
                                { label: "Pending", value: "pending" },
                                { label: "Failed", value: "failed" },
                            ]}
                            value={statusFilter}
                            onChange={setStatusFilter}
                        />
                        <DataTable
                            key={requests.length}
                            columnContentTypes={["text", "text", "text"]}
                            headings={["Image URL", "Status", "Timestamp"]}
                            rows={rows}
                        />
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
};

export default Dashboard;
