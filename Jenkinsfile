pipeline {
    agent any
    
    environment {
        DOCKER_COMPOSE_VERSION = '2.21.0'
        APP_NAME = 'laravel-app'
    }
    
    stages {
        stage('Checkout') {
            steps {
                script {
                    checkout scm
                }
            }
        }
        
        stage('Build') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose build --no-cache'
                    } else {
                        bat 'docker-compose build --no-cache'
                    }
                }
            }
        }
        
        stage('Laravel Setup') {
            steps {
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose up -d
                            sleep 30  # Wait for MySQL to be ready
                            docker-compose exec -T app composer install
                            docker-compose exec -T app cp .env.example .env
                            docker-compose exec -T app php artisan key:generate
                            docker-compose exec -T app php artisan migrate --force
                            docker-compose exec -T app npm install
                            docker-compose exec -T app npm run build
                        '''
                    } else {
                        bat '''
                            docker-compose up -d
                            timeout /t 30
                            docker-compose exec -T app composer install
                            docker-compose exec -T app cp .env.example .env
                            docker-compose exec -T app php artisan key:generate
                            docker-compose exec -T app php artisan migrate --force
                            docker-compose exec -T app npm install
                            docker-compose exec -T app npm run build
                        '''
                    }
                }
            }
        }
        
        stage('Test') {
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose exec -T app php artisan test'
                    } else {
                        bat 'docker-compose exec -T app php artisan test'
                    }
                }
            }
        }
        
        stage('Deploy') {
            when {
                branch 'main'
            }
            steps {
                script {
                    if (isUnix()) {
                        sh 'docker-compose up -d'
                    } else {
                        bat 'docker-compose up -d'
                    }
                }
            }
        }
        
        stage('Cleanup') {
            steps {
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose down
                            docker system prune -f
                        '''
                    } else {
                        bat '''
                            docker-compose down
                            docker system prune -f
                        '''
                    }
                }
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