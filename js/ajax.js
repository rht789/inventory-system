// js/ajax.js
export async function apiGet(path) {
  const res = await fetch(path);
  if (!res.ok) throw new Error(`${res.status} ${res.statusText}`);
  return res.json();
}

export async function apiPost(path, body) {
  try {
    console.log(`Sending POST to ${path} with data:`, body);
    
    const res = await fetch(path, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body),
    });
    
    // First try to parse the response as JSON
    let responseData;
    const responseText = await res.text();
    
    try {
      responseData = JSON.parse(responseText);
    } catch (e) {
      console.error("Failed to parse response as JSON:", responseText);
      throw new Error(`${res.status} ${res.statusText} - Invalid JSON response`);
    }
    
    // Check if the request was successful
    if (!res.ok) {
      console.error("API Error:", responseData);
      throw new Error(`${res.status} ${res.statusText} - ${responseData.message || 'Unknown error'}`);
    }
    
    return responseData;
  } catch (error) {
    console.error("API Call Failed:", error);
    throw error;
  }
}
