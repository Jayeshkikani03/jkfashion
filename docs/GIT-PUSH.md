# JK Fashion — GitHub commit & push (token guide)

Repo: https://github.com/Jayeshkikani03/jkfashion  
Local folder: `C:\Users\sspl1386\Local Sites\dynamicblog\app\public`

Use this when you want to **commit on PC** and **push to GitHub**.

---

## 1. Which token to use

Use a **GitHub Personal Access Token (PAT)** for account **`Jayeshkikani03`** (owner of `jkfashion`).

Do **not** use:

- Another GitHub account token (example: work account `SSPL1089`) → push fails with **403**
- An old token without write permission → also **403**
- Password login (GitHub no longer accepts account password for git push)

### Best choice: Fine-grained token

| Setting | Value |
|---------|--------|
| Token name | `jkfashion-push` |
| Resource owner | `Jayeshkikani03` |
| Repository access | Only select **`jkfashion`** |
| Permissions → **Contents** | **Read and write** |
| Permissions → **Metadata** | Read-only (auto) |
| Expiration | 30–90 days (or custom) |

### Alternative: Classic token

| Setting | Value |
|---------|--------|
| Note | `jkfashion-push` |
| Scopes | tick **`repo`** (full control of private repositories) |
| Use for | if fine-grained is confusing |

---

## 2. Create the token (one time)

1. Login GitHub as **Jayeshkikani03**
2. Open: https://github.com/settings/tokens  
   Or: Profile → **Settings** → **Developer settings** → **Personal access tokens**
3. **Generate new token** (Fine-grained recommended)
4. Set permissions from the table above
5. Click **Generate**
6. **Copy the token once** (starts with `github_pat_` or `ghp_`)  
   Save it in a password manager — GitHub will not show it again

---

## 3. Commit on your PC (every time you change code)

Open terminal in the site `public` folder:

```powershell
cd "C:\Users\sspl1386\Local Sites\dynamicblog\app\public"

git status
git add -A
git commit -m "Describe your change in one short sentence"
```

If commit says “nothing to commit”, there are no new changes.

---

## 4. Push with your token (do not change Windows git login)

Your PC may be logged into another GitHub user.  
Push with the **Jayeshkikani03 token only for this command** (does not permanently change local login):

```powershell
cd "C:\Users\sspl1386\Local Sites\dynamicblog\app\public"

git -c credential.helper= push "https://x-access-token:PASTE_TOKEN_HERE@github.com/Jayeshkikani03/jkfashion.git" main
```

Replace `PASTE_TOKEN_HERE` with your real token.

Then sync tracking (optional):

```powershell
git -c credential.helper= fetch origin
git status
```

You should see: `main...origin/main` (not ahead/behind).

---

## 5. What NOT to do

| Don’t | Why |
|-------|-----|
| Put token inside `HOSTING.md` / code / chat | Anyone can steal it |
| Commit `wp-config.php` | Has DB password |
| Commit `wp-content/uploads/` | Too large; ignored on purpose |
| Share token in WhatsApp/email screenshots | Revoke and make a new one |
| Use SSPL / company GitHub account for this repo | Wrong owner → 403 |

---

## 6. If push fails

| Error | Fix |
|-------|-----|
| `denied to SSPL1089` | Use Jayesh token + `credential.helper=` command above |
| `Resource not accessible by personal access token` | Edit token → **Contents: Read and write** on `jkfashion` |
| `Authentication failed` | Token expired/revoked → create new token |
| `rejected (non-fast-forward)` | Run `git pull origin main` first, then push again |

---

## 7. Security checklist

1. Keep repo **private** if it has business content  
2. Revoke any token you ever pasted in chat  
3. After push, you can delete the token from the command history if needed  
4. Never store the token inside the project files  

---

## 8. Quick copy checklist

1. [ ] Token for **Jayeshkikani03**  
2. [ ] **Contents: Read and write** on repo `jkfashion`  
3. [ ] `git add` + `git commit` locally  
4. [ ] Push with `x-access-token:YOUR_TOKEN@github.com/Jayeshkikani03/jkfashion.git`  
5. [ ] Check GitHub website — latest commit visible  

Related docs:

- Local setup → [`SETUP.md`](SETUP.md)  
- Hosting live site → [`HOSTING.md`](HOSTING.md)
