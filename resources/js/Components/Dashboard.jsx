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

    useEffect(() => {
        const token = localStorage.getItem("token");
        if (!token) {
            window.location.href = "/login";
            return;
        }
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        fetchData();
        fetchNotifications();
        fetchUser();
    }, [statusFilter]);

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
            console.log("Fetched data:", response.data);
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
        try {
            await axios.post("/api/bulk-requests", { urls });
            setUrls("");
            await fetchData();
        } catch (err) {
            console.error("Submission failed:", err.response.data.error);
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
    console.log("Rows updated:", rows);

    return (
        <Page title="Dashboard">
            {notifications.length > 0 && (
                <Banner title="Notifications" status="info">
                    <List>
                        {notifications.map((notif) => (
                            <List.Item key={notif.id}>
                                {notif.data.message}
                                <Button
                                    plain
                                    onClick={() => markAsRead(notif.id)}
                                >
                                    Mark as Read
                                </Button>
                            </List.Item>
                        ))}
                    </List>
                </Banner>
            )}

            <Card sectioned>
                <FormLayout>
                    <TextField
                        label="Image URLs (one per line)"
                        multiline={4}
                        value={urls}
                        onChange={setUrls}
                    />
                    <Button primary onClick={handleSubmit}>
                        Submit
                    </Button>
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
                    key={requests.length} // Forces re-render on state change
                    columnContentTypes={["text", "text", "text"]}
                    headings={["Image URL", "Status", "Timestamp"]}
                    rows={rows}
                />
            </Card>
        </Page>
    );
};

export default Dashboard;
