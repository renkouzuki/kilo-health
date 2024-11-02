pipeline {
    agent any

    environment {
        DOCKER_COMPOSE_VERSION = '2.21.0'
        APP_NAME = 'laravel-app'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build') {
            steps {
                sh 'docker-compose build --no-cache'
            }
        }

        stage('Laravel Setup') {
            steps {
                sh 'docker-compose up -d'
                sh 'docker-compose exec -T app composer install'
                sh 'docker-compose exec -T app cp .env.example .env'
                sh 'docker-compose exec -T app php artisan key:generate'
                sh 'docker-compose exec -T app php artisan migrate --force'
                sh 'docker-compose exec -T app npm install'
                sh 'docker-compose exec -T app npm run build'
            }
        }

        stage('Test') {
            steps {
                sh 'docker-compose exec -T app php artisan test'
            }
        }

        stage('Deploy') {
            when {
                branch 'main'  // Only deploy from main branch
            }
            steps {
                sh 'docker-compose up -d'
            }
        }

        stage('Cleanup') {
            steps {
                sh 'docker-compose down'
                sh 'docker system prune -f'
            }
        }
    }

    post {
        always {
            cleanWs()
        }
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}