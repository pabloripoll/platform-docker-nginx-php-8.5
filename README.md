<div id="top-header" style="with:100%;height:auto;text-align:right;">
    <img src="./resources/docs/images/pr-banner-long.png">
</div>

# INFRASTRUCTURE PLATFORM FOR PHP 8.5

[![Generic badge](https://img.shields.io/badge/version-1.0-blue.svg)](https://shields.io/)
[![Open Source? Yes!](https://badgen.net/badge/Open%20Source%20%3F/Yes%21/blue?icon=github)](./)
[![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)](./LICENSE)

This Infrastructure Platform repository is designed for back-end projects and provides separate platforms:

- **REST API Platform:** Linux Alpine version 3.23 + NGINX version 1.28 *(or latest by APK)* + PHP 8.5

The goal of this repository is to offer developers a consistent framework for local development, mirroring real-world deployment scenarios. In production, the API may be deployed on an AWS EC2 / GCP GCE or instance or distributed across Kubernetes pods, while the database would reside on an AWS RDS instance. thus, network connection between platforms are decoupled.

Platform engineering is the discipline of creating and managing an internal developer platform (IDP) to provide developers with self-service tools, automated workflows, and standardized environments. By reducing cognitive load and complexity, it allows software engineering teams to innovate faster and more efficiently, building on the principles of DevOps. The IDP acts like a product, where developers are customers, and aims to streamline the entire software development lifecycle, from building and testing to deploying and monitoring.

### Key principles and goals

- Self-service: Provide developers with easy-to-use tools and automated workflows to manage their own infrastructure needs without having to file tickets or rely on other teams.
- Standardization: Use standardized tools and environments to ensure consistency, reliability, and security across projects.
- Reduced cognitive load: Abstract away underlying complexity so developers can focus on writing code and delivering business value rather than managing infrastructure details.
- Developer experience: Build a positive and productive environment for developers, making them feel empowered and less frustrated.
- Operational efficiency: Automate repetitive tasks and standardize processes to improve the speed and reliability of software delivery.

### How it works

- Internal Developer Platform (IDP): A dedicated platform built by the platform engineering team that provides a curated set of tools, services, and infrastructure.
- Golden Paths: Predefined, optimized workflows and best practices that developers can follow to accomplish common tasks quickly and easily.
- Treating the platform as a product: Platform engineers treat their IDP like a product, with developers as their customers, to ensure it meets the needs of the organization.

### Documentation:

- [What is platform engineering? - IBM](https://www.ibm.com/think/topics/platform-engineering)
- [Understanding platform engineering - Red Hat](https://www.redhat.com/en/topics/platform-engineering)
- [Platform engineering - Prescriptive Guidance - AWS](https://docs.aws.amazon.com/prescriptive-guidance/latest/aws-caf-platform-perspective/platform-eng.html)
- [What is an internal developer platform (IDP)? - Google Cloud](https://cloud.google.com/solutions/platform-engineering)
- [What is platform engineering? - Microsoft](https://learn.microsoft.com/en-us/platform-engineering/what-is-platform-engineering)
- [What is Platform engineering? - Github](https://github.com/resources/articles/what-is-platform-engineering)
<br><br>

## <a id="platform-usage"></a>Use this Platform Repository for your own REST API repositories

Platform Documentation:

- [NGINX + PHP 8.5](./platform/nginx-php-8.5/README.md)
<br><br>

Repository directories structure overview:
```bash
.
├── apirest         # Core directory binded in Docker main container for back-end
│   └── ...         # sub-module or detach with the real project respository
│
├── platform
│   └── nginx-php-8.5
│       ├── Makefile
│       └── docker
│           ├── Dockerfile
│           ├── config
│           │   ├── crontab
│           │   ├── nginx
│           │   │   ├── conf.d
│           │   │   │   └── default.conf
│           │   │   └── nginx.conf
│           │   ├── php
│           │   │   └── conf.d
│           │   │       ├── fpm-pool.conf
│           │   │       └── php.ini
│           │   └── supervisor
│           │       ├── conf.d
│           │       │   ├── nginx.conf
│           │       │   └── php-fpm.conf
│           │       └── supervisord.conf
│           ├── docker-compose.network.yml
│           ├── docker-compose.yml
│           └── docker-entrypoint.sh
│
├── resources
│   ├── apirest         # sub-module or detach with the real project respository
│   ├── apirest-sample
│   │   └── ...         # default application to mount on installation
│   ├── automation
│   │   ├── local
│   │   │   ├── Makefile
│   │   │   └── Makefile.child
│   │   └── remote
│   │       └── Makefile
│   ├── databases
│   │   └── ...
│   └── docs
│       └── images
│           ├── pr-banner-long.png
│           └── ...
│
├── .env          # Platforms main values applied
├── .env.example  # Platforms main values example
├── Makefile      # Automated commands into recipes
└── README.md
```
<br>

Set up platforms

- Copy `.env.example` to `.env` and adjust platforms settings (rest api port, database port, mail service port, container RAM usage, etc.)
- By configuring the REST API container with e.g. `APIREST_CAAS_MEM=128M` *(CAAS = Container As A Service)*, remember to set the same RAM value into container local configuration files that will be mounted into the container.
<br>

Here’s a step-by-step guide for using this Platform repository along with your own REST API repository:

- Remove the existing `./apirest` directory contents from local and from git cache
- Install your desired repository inside `./apirest`
- Choose between Git submodule and detached repository approaches

### Estimated consumption

This is the overview of the estimated host machine consumption:
- *Your local values could be slightly different*
- *(?) Your custom project abbreviation*
```
$ sudo docker stats

CONTAINER ID   NAME              CPU %     MEM USAGE / LIMIT   MEM %     NET I/O           BLOCK I/O         PIDS
fad4ece8a496   (?)-apirest-dev   0.04%     55.68MiB / 512MiB   10.87%    35.1kB / 25.8kB   1.65MB / 3.5MB    11
```

### Managing the `apirest` Directory: Submodule vs Detached Repository

To remove the default installation content in `./apirest/` directory with and install your repository instead, there are two alternatives for managing both the Platform and REST API repositories independently:

#### 1. **GIT Sub-module**

> Git commands can be executed **only from inside the container**.

- Remove `apirest` from local and git cache:
  ```bash
  $ rm -rfv ./apirest/* ./apirest/.[!.]*$
  $ git rm -r --cached apirest
  $ git commit -m "maint: apirest directory and its default installation removed to detach from platform repository"
  ```

- Add the desired repository as a submodule:
  ```bash
  $ git submodule add git@[vcs]:[account]/[repository].git ./apirest
  $ git commit -m "maint: apirest added as a git submodule"
  ```

- To update submodule contents:
  ```bash
  $ cd ./apirest
  $ git pull origin main  # or desired branch
  ```

- To initialize/update submodules after `git clone`:
  ```bash
  $ git submodule update --init --recursive
  ```
<br>

#### 2. **GIT Detached Repository (Recommended)**

> Git commands can be executed **whether from inside the container or on the local machine**.

- Remove `apirest` from local and git cache:
  ```bash
  $ git rm -r --cached -- "apirest/*" ":(exclude)apirest/.gitkeep"
  $ git clean -fd
  $ git reset --hard
  $ git commit -m "Remove apirest directory and its default installation"
  ```

- Clone the desired repository as a detached repository:
  ```bash
  $ git clone git@[vcs]:[account]/[repository].git ./apirest
  ```

- The `apirest` directory is now an **independent repository**, not tracked as a submodule in your main repo. You can use `git` commands freely inside `apirest` from anywhere.
<br>

#### **Summary Table**

| Approach         | Repo independence | Where to run git commands | Use case                        |
|------------------|------------------|--------------------------|----------------------------------|
| Submodule        | Tracked by main  | Inside container         | Main repo controls webapp version|
| Detached (rec.)  | Fully independent| Local or container       | Maximum flexibility              |

> **Note**: After new project cloned inside `./apirest`, consider adding `./apirest/.gitkeep` in it to prevent accidental tracking *(especially for detached repository)*.

<br>

## Contributing

Contributions are very welcome! Please open issues or submit PRs for improvements, new features, or bug fixes.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -am 'feat: Add new feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Create a new Pull Request
<br><br>

## License

This project is open-sourced under the [MIT license](LICENSE).

<!-- FOOTER -->
<br>

---

<br>

- [GO TOP ⮙](#top-header)

<div style="with:100%;height:auto;text-align:right;">
    <img src="./resources/docs/images/pr-banner-long.png">
</div>