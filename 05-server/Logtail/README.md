# ログ監視

## Environment

- Docker: `19.03.12`
    - docker-compose: `1.26.0`

### Structure
```bash
./
|_ docker/
|  |_ amazonlinux2/ # amazonlinux2 service container
|  |  |_ Dockerfile
|  |
|  |_ centos6/ # centos6 service container
|     |_ Dockerfile
|
|_ docker-compose.yml # docker containers building file
```

### Docker
- services:
    - **amazonlinux2**: `amazonlinux:2`
        - 
